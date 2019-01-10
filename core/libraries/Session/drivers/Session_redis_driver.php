<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Null_Session_redis_driver extends Null_Session_driver implements SessionHandlerInterface {

    protected $_redis;
    protected $_key_prefix = 'null_session:';
    protected $_lock_key;

    // ------------------------------------------------------------------------

    public function __construct(&$params) {
        parent::__construct($params);

        if (empty($this->_config['save_path'])) {
            log_message('error', 'Session: No Redis save path configured.');
        } elseif (preg_match('#(?:tcp://)?([^:?]+)(?:\:(\d+))?(\?.+)?#', $this->_config['save_path'], $matches)) {
            isset($matches[3]) OR $matches[3] = ''; // Just to avoid undefined index notices below
            $this->_config['save_path'] = array(
                'host' => $matches[1],
                'port' => empty($matches[2]) ? NULL : $matches[2],
                'password' => preg_match('#auth=([^\s&]+)#', $matches[3], $match) ? $match[1] : NULL,
                'database' => preg_match('#database=(\d+)#', $matches[3], $match) ? (int) $match[1] : NULL,
                'timeout' => preg_match('#timeout=(\d+\.\d+)#', $matches[3], $match) ? (float) $match[1] : NULL
            );

            preg_match('#prefix=([^\s&]+)#', $matches[3], $match) && $this->_key_prefix = $match[1];
        } else {
            log_message('error', 'Session: Invalid Redis save path format: ' . $this->_config['save_path']);
        }

        if ($this->_config['match_ip'] === TRUE) {
            $this->_key_prefix .= $_SERVER['REMOTE_ADDR'] . ':';
        }
    }

    // ------------------------------------------------------------------------

    public function open($save_path, $name) {
        if (empty($this->_config['save_path'])) {
            return FALSE;
        }

        $redis = new Redis();
        if (!$redis->connect($this->_config['save_path']['host'], $this->_config['save_path']['port'], $this->_config['save_path']['timeout'])) {
            log_message('error', 'Session: Unable to connect to Redis with the configured settings.');
        } elseif (isset($this->_config['save_path']['password']) && !$redis->auth($this->_config['save_path']['password'])) {
            log_message('error', 'Session: Unable to authenticate to Redis instance.');
        } elseif (isset($this->_config['save_path']['database']) && !$redis->select($this->_config['save_path']['database'])) {
            log_message('error', 'Session: Unable to select Redis database with index ' . $this->_config['save_path']['database']);
        } else {
            $this->_redis = $redis;
            return TRUE;
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    public function read($session_id) {
        if (isset($this->_redis) && $this->_get_lock($session_id)) {
            // Needed by write() to detect session_regenerate_id() calls
            $this->_session_id = $session_id;

            $session_data = (string) $this->_redis->get($this->_key_prefix . $session_id);
            $this->_fingerprint = md5($session_data);
            return $session_data;
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    public function write($session_id, $session_data) {
        if (!isset($this->_redis)) {
            return FALSE;
        }
        // Was the ID regenerated?
        elseif ($session_id !== $this->_session_id) {
            if (!$this->_release_lock() OR ! $this->_get_lock($session_id)) {
                return FALSE;
            }

            $this->_fingerprint = md5('');
            $this->_session_id = $session_id;
        }

        if (isset($this->_lock_key)) {
            $this->_redis->setTimeout($this->_lock_key, 300);
            if ($this->_fingerprint !== ($fingerprint = md5($session_data))) {
                if ($this->_redis->set($this->_key_prefix . $session_id, $session_data, $this->_config['expiration'])) {
                    $this->_fingerprint = $fingerprint;
                    return TRUE;
                }

                return FALSE;
            }

            return $this->_redis->setTimeout($this->_key_prefix . $session_id, $this->_config['expiration']);
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    public function close() {
        if (isset($this->_redis)) {
            try {
                if ($this->_redis->ping() === '+PONG') {
                    isset($this->_lock_key) && $this->_redis->delete($this->_lock_key);
                    if (!$this->_redis->close()) {
                        return FALSE;
                    }
                }
            } catch (RedisException $e) {
                log_message('error', 'Session: Got RedisException on close(): ' . $e->getMessage());
            }

            $this->_redis = NULL;
            return TRUE;
        }

        return TRUE;
    }

    // ------------------------------------------------------------------------

    public function destroy($session_id) {
        if (isset($this->_redis, $this->_lock_key)) {
            if (($result = $this->_redis->delete($this->_key_prefix . $session_id)) !== 1) {
                log_message('debug', 'Session: Redis::delete() expected to return 1, got ' . var_export($result, TRUE) . ' instead.');
            }

            return $this->_cookie_destroy();
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    public function gc($maxlifetime) {
        // Not necessary, Redis takes care of that.
        return TRUE;
    }

    // ------------------------------------------------------------------------

    protected function _get_lock($session_id) {
        if (isset($this->_lock_key)) {
            return $this->_redis->setTimeout($this->_lock_key, 300);
        }

        // 30 attempts to obtain a lock, in case another request already has it
        $lock_key = $this->_key_prefix . $session_id . ':lock';
        $attempt = 0;
        do {
            if (($ttl = $this->_redis->ttl($lock_key)) > 0) {
                sleep(1);
                continue;
            }

            if (!$this->_redis->setex($lock_key, 300, time())) {
                log_message('error', 'Session: Error while trying to obtain lock for ' . $this->_key_prefix . $session_id);
                return FALSE;
            }

            $this->_lock_key = $lock_key;
            break;
        } while (++$attempt < 30);

        if ($attempt === 30) {
            log_message('error', 'Session: Unable to obtain lock for ' . $this->_key_prefix . $session_id . ' after 30 attempts, aborting.');
            return FALSE;
        } elseif ($ttl === -1) {
            log_message('debug', 'Session: Lock for ' . $this->_key_prefix . $session_id . ' had no TTL, overriding.');
        }

        $this->_lock = TRUE;
        return TRUE;
    }

    // ------------------------------------------------------------------------

    protected function _release_lock() {
        if (isset($this->_redis, $this->_lock_key) && $this->_lock) {
            if (!$this->_redis->delete($this->_lock_key)) {
                log_message('error', 'Session: Error while trying to free lock for ' . $this->_lock_key);
                return FALSE;
            }

            $this->_lock_key = NULL;
            $this->_lock = FALSE;
        }

        return TRUE;
    }

}
