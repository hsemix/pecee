<?php
namespace Pecee\Controller\File;

use Pecee\Boolean;
use Pecee\Controller\ControllerBase;
use Pecee\Module;
use Pecee\Web\Minify\CSSMin;

abstract class FileAbstract extends ControllerBase {

    protected $files;
    protected $type;
    protected $tmpDir;

    const TYPE_JAVASCRIPT = 'js';
    const TYPE_CSS = 'css';

    public static $types=array(self::TYPE_JAVASCRIPT, self::TYPE_CSS);

    public function __construct($type) {

        parent::__construct();

        if(!in_array($type, self::$types)) {
            throw new \InvalidArgumentException(sprintf('Unknown type, must be one of the following: %s', join(', ', self::$types)));
        }

        $this->type = $type;
        $this->tmpDir = $_ENV['base_path'] . DIRECTORY_SEPARATOR . 'cache';
    }

    public function wrap() {

        $this->files = $this->input('files');

        // Set time limit
        set_time_limit(60);

        if($this->debugMode() === false) {
            $lastModified = filemtime($this->getTempFile());
            $md5 = md5_file($this->getTempFile());

            if(!in_array('ob_gzhandler', ob_list_handlers())) {
                ob_start ('ob_gzhandler');
            }

            // Set headers
            response()->cache($md5, $lastModified);
        }

        // Set headers
        response()->headers([
            'Content-type: '.$this->getHeader(),
            'Charset: ' . $this->_site->getCharset(),
        ]);

        if($this->debugMode() === false && is_dir($this->tmpDir)) {
            $handle = opendir($this->tmpDir);
            while (false !== ($file = readdir($handle))) {
                if($file === (md5($this->files) . '.' . $this->type)) {
                    unlink($this->tmpDir . DIRECTORY_SEPARATOR . $file);
                }
            }
            closedir($handle);
        }

        if(!file_exists($this->getTempFile())) {
            $this->saveTempFile();
        }

        echo file_get_contents($this->getTempFile(), FILE_USE_INCLUDE_PATH);
    }

    protected function saveTempFile() {
        if($this->files) {
            $files = (strpos($this->files, ',')) ? explode(',', $this->files) : array($this->files);
            if(count($files)) {
                /* Begin wrapping */
                if(!is_dir($this->tmpDir)) {
                    mkdir($this->tmpDir, 0777, true);
                }
                $handle = fopen($this->getTempFile(), 'w+', FILE_USE_INCLUDE_PATH);
                if($handle) {
                    foreach($files as $index => $file) {
                        $content = null;
                        $filePath = 'www/' . $this->getPath() . $file;

                        if(stream_resolve_include_path($filePath) !== false) {
                            $content = file_get_contents($filePath, FILE_USE_INCLUDE_PATH);
                        } else {
                            $modules = Module::getInstance()->getList();
                            if($modules) {
                                foreach($modules as $module) {
                                    $moduleFilePath = $module . DIRECTORY_SEPARATOR . $filePath;
                                    if(file_exists($moduleFilePath)) {
                                        $content = file_get_contents($moduleFilePath);
                                        break;
                                    }
                                }
                            }
                        }

                        if(!$content) {
                            // Try resources folder
                            $filePath = dirname(dirname(dirname(__DIR__))) . '/resources/' . $this->getPath() . $file;
                            if(file_exists($filePath)) {
                                $content = file_get_contents($filePath, FILE_USE_INCLUDE_PATH);
                            }
                        }

                        if($content) {
                            if($this->type === self::TYPE_CSS && !$this->debugMode()) {
                                $content = CSSMin::process($content);
                            }

                            $buffer = '/* '.strtoupper($this->type).': ' . $file . ' */' . chr(10);
                            $buffer.= $content;

                            if( $index < count($files)-1 ) {
                                $buffer .= str_repeat(chr(10),2);
                            }
                            fwrite($handle, $buffer);
                        }
                    }
                    fclose($handle);
                    chmod($this->getTempFile(), 0777);
                }
            }
        }
    }
    protected function debugMode() {
        return Boolean::parse(env('DEBUG'));
    }

    protected function getHeader() {
        switch($this->type) {
            case self::TYPE_CSS:
                return 'text/css';
                break;
            case self::TYPE_JAVASCRIPT:
                return 'application/javascript';
                break;
        }
        return '';
    }

    protected function getPath() {
        switch($this->type) {
            case self::TYPE_JAVASCRIPT:
                return $this->_site->getJsPath();
                break;
            case self::TYPE_CSS:
                return $this->_site->getCssPath();
                break;
        }
        return '';
    }

    protected function getTempFile() {
        return $this->tmpDir.DIRECTORY_SEPARATOR.md5($this->files) . '.' . $this->type;
    }
}