<?php


namespace Ctfang\LaravelWatch;


class Factory
{
    /**
     * @param  \Ctfang\LaravelWatch\LoadLogic  $logic
     */
    public function make(LoadLogic $logic)
    {
        $allMes = [];
        foreach ($logic->getWatchClass() as $watchName => $config) {
            foreach ($config['call'] as $name => $calls) {
                /** @var \ReflectionMethod $ref */
                $ref            = $calls['ref'];
                $func           = $ref->getName();
                $modifier       = \Reflection::getModifierNames($ref->getModifiers())[0];
                $parNameAndType = [];
                $parName        = [];
                $pars        = [];
                $return         = '';

                foreach ($ref->getParameters() as $item) {
                    $par  = $item->getName();
                    $type = (string) $item->getType();
                    if ($item->isDefaultValueAvailable()) {
                        $parNameAndType[] = "{$type} \${$par} = ".$item->getDefaultValue();
                    } else {
                        $parNameAndType[] = "{$type} \${$par}";
                    }
                    $parName[] = "\${$par}";
                    $pars[]    = "'{$par}' => \${$par}";
                }

                $ret = $ref->getReturnType();
                if ($ret) {
                    $return = ":".($ret->allowsNull() ? "?\\" : "\\").$ret;
                }

                $watch                     = $this->getCallStr($calls['met']);
                $allMes[$watchName][$name] = str_replace([
                    '{modifier}',
                    '{func}',
                    '{parNameAndType}',
                    '{return}',
                    '{parNameUse}',
                    '{parName}',
                    '{pars}',
                    '{watch}',
                ], [
                    $modifier,
                    $func,
                    implode(", ", $parNameAndType),
                    $return,
                    $parName ? (" use (".implode(", ", $parName)).")" : "",
                    implode(", ", $parName),
                    "[" . implode(", ", $pars) . "]",
                    $watch,
                ], $this->funcStr());
            }
        }

        $makeDir = $logic->watchCachePath;
        if (!is_dir($makeDir)) {
            mkdir($makeDir, 0755, true);
        }

        foreach ($allMes as $watchName => $allMe) {
            $watchNameMd5 = str_replace('\\', '_', $watchName);
            $str          = $this->classStr();
            $str          = str_replace([
                '{class}',
                '{call}',
                '{body}',
            ], [
                $watchNameMd5,
                "\\".$watchName,
                implode($allMe, "\n"),
            ], $str);
            file_put_contents($makeDir.$watchNameMd5.'.php', $str);
        }
    }

    private function getCallStr(array $met): string
    {
        $arr = [];
        foreach ($met as $item) {
            $arr[] = "['{$item[0]}', '{$item[1]}']";
        }
        $str = implode(",\n            ", $arr);
        return "[\n            {$str}\n        ]";
    }

    private function classStr(): string
    {
        return file_get_contents(__DIR__.'/../template/watch.template');
    }

    private function funcStr(): string
    {
        return file_get_contents(__DIR__.'/../template/func.template');
    }

    public function clear(LoadLogic $logic)
    {
        @unlink($logic->bindCachePath);
        @unlink($logic->fileCachePath);

        foreach (scandir($logic->watchCachePath) as $file) {
            if (!in_array($file, ['.', '..'])) {
                @unlink($file);
            }
        }
    }
}