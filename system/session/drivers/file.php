<?php

namespace System\Session\Drivers;

defined('DS') or exit('No direct script access.');

class File extends Driver implements Sweeper
{
    /**
     * Path tempat menyimpan file session.
     *
     * @var string
     */
    private $path;

    /**
     * Buat instance baru driver session File.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Muat session berdasarkan ID yang diberikan.
     * Jika session tidak ditemukan, NULL akan direturn.
     *
     * @param string $id
     *
     * @return array
     */
    public function load($id)
    {
        $path = $this->path.$this->naming($id);

        if (is_file($path)) {
            $path = file_get_contents($path);
            return unserialize($this->unguard($path));
        }
    }

    /**
     * Simpan session.
     *
     * @param array $session
     * @param array $config
     * @param bool  $exists
     */
    public function save($session, $config, $exists)
    {
        $path = $this->path.$this->naming($session['id']);
        $session = $this->guard(serialize($session));

        file_put_contents($path, $session, LOCK_EX);
    }

    /**
     * Hapus session berdasarkan ID yang diberikan.
     *
     * @param string $id
     */
    public function delete($id)
    {
        $path = $this->path.$this->naming($id);

        if (is_file($path)) {
            @unlink($this->path.$id);
        }
    }

    /**
     * Hapus seluruh session yang telah kedaluwarsa.
     *
     * @param int $expiration
     */
    public function sweep($expiration)
    {
        $files = glob($this->path.'*.session.php');

        if (false === $files || ! is_array($files) || 0 === count($files)) {
            return;
        }

        foreach ($files as $file) {
            if ('file' === filetype($file) && filemtime($file) < $expiration) {
                @unlink($file);
            }
        }
    }

    /**
     * Helper untuk format nama file session.
     *
     * @param string $id
     *
     * @return string
     */
    protected function naming($id)
    {
        return sprintf('%u', crc32($id)).'.session.php';
    }

    /**
     * Helper untuk proteksi akses file via browser.
     *
     * @param string $value
     *
     * @return string
     */
    protected static function guard($value)
    {
        $guard = "<?php defined('DS') or exit('No direct script access.');?>";
        return $guard.$value;
    }

    /**
     * Helper untuk buang proteksi akses file via browser.
     * (Kebalikan dari method guard).
     *
     * @param string $value
     *
     * @return string
     */
    protected static function unguard($value)
    {
        $guard = "<?php defined('DS') or exit('No direct script access.');?>";
        return str_replace($guard, '', $value);
    }
}
