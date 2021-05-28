<?php


namespace Ctfang\LaravelWatch;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use ReflectionClass;

class LoadLogic
{
    private $reader;

    protected $watch = [];

    public $baseNamespace = [
        "App\\"
    ];

    protected $fileCache = [];

    public $fileCachePath;
    public $bindCachePath;
    public $watchCachePath;

    public const watchCachePath = 'bootstrap/watch/';

    /**
     * LoadLogic constructor.
     */
    public function __construct()
    {
        /** @var \Composer\Autoload\ClassLoader $load */
        $load      = include base_path('/vendor/autoload.php');
        $psr4      = $load->getPrefixesPsr4();
        $psr4ToDir = [];
        foreach ($this->baseNamespace as $namespace) {
            $psr4ToDir[$namespace] = $psr4[$namespace] ?? [];
        }
        $this->baseNamespace = $psr4ToDir;
        $this->reader        = new AnnotationReader();

        $this->fileCachePath  = base_path('storage/watch').'/file_cache.php';
        $this->bindCachePath  = base_path('storage/watch').'/bind_cache.php';
        $this->watchCachePath = base_path(self::watchCachePath);
    }

    /**
     * @param $path
     * @return array
     */
    private function getDir($path): array
    {
        $arr = array();
        if (is_dir($path)) {
            $data = scandir($path);
            if (!empty($data)) {
                foreach ($data as $value) {
                    if ($value != '.' && $value != '..') {
                        $sub_path = $path."/".$value;
                        $temp     = $this->getDir($sub_path);
                        $arr      = array_merge($temp, $arr);
                    }
                }
            }
        } elseif (pathinfo($path, PATHINFO_EXTENSION) == 'php') {
            $arr[] = realpath($path);
        }

        return $arr;
    }

    /**
     * @throws \ReflectionException
     */
    public function load()
    {
        try {
            $cache           = include $this->fileCachePath;
            $this->fileCache = (array) $cache;
        } catch (\Throwable $exception) {
            $this->fileCache = [];
        }

        foreach ($this->baseNamespace as $namespace => $arrDir) {
            foreach ($arrDir as $dir) {
                $this->scanNamespace($namespace, realpath($dir));
            }
        }

        if ($this->watch) {
            $dir = dirname($this->fileCachePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                file_put_contents($dir.'/.gitignore', '*');
            }
            file_put_contents(
                $this->fileCachePath,
                "<?php // 不参与运行,加速解析注解 \nreturn ".var_export($this->fileCache, true).";"
            );
        }
    }

    /**
     * @throws \ReflectionException
     */
    protected function scanNamespace(string $namespace, string $dir)
    {
        foreach ($this->getDir($dir) as $phpFile) {
            $fileCtime = filectime($phpFile);
            $old       = $this->fileCache[$phpFile] ?? 0;
            if ($old == $fileCtime) {
                continue;
            }

            $this->fileCache[$phpFile] = $fileCtime;
            $class = str_replace([$dir, '.php'], [$namespace, ''], $phpFile);
            $class = str_replace(["/", "\\\\"], ['\\', '\\'], $class);

            $this->filClass($class, $phpFile);
        }
    }

    /**
     * @param  string  $class
     * @param  string  $phpFile
     * @throws \ReflectionException
     */
    private function filClass(string $class, string $phpFile)
    {
        @unlink($this->watchCachePath . str_replace(['\\'], ['_'], $class). '.php');

        foreach ((new ReflectionClass($class))->getMethods() as $method) {
            foreach ($this->reader->getMethodAnnotations($method) as $methodAnnotation) {
                $this->watch[$class]['file']                              = $phpFile;
                $this->watch[$class]['call'][$method->getName()]['ref']   = $method;
                $this->watch[$class]['call'][$method->getName()]['met'][] = [
                    $methodAnnotation->class, $methodAnnotation->func
                ];
            }
        }
    }

    public function getWatchClass(): array
    {
        return $this->watch;
    }
}