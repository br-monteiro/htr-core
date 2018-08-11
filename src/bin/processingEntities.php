#!/usr/bin/php
<?php

require_once '../../vendor/autoload.php';

use App\System\Configuration as cfg;

$path = cfg::PATH_ENTITIES . '/';

$files = array_filter(scandir($path), function($v) {
    return (bool) preg_match('/\.php$/', $v);
});

function readFileContent($file, $asArray = false)
{
    if ($asArray) {
        return file($file);
    }

    return file_get_contents($file);
}

function changeFile($fileName, $fileContent = '', $fileMode = 'w+')
{
    if (file_exists($fileName) && !is_writable($fileName)) {
        echo "[ERROR] Arquivo sem permissão de escrita.";
        return false;
    }
    $file = fopen($fileName, $fileMode);
    if (!$file) {
        return false;
    }
    if (fwrite($file, $fileContent) === false) {
        fclose($file);
        return false;
    }
    if (!fclose($file)) {
        return false;
    }
    return true;
}

function normalizeFieldName($str, $capitaliseFirstChar = true)
{
    if ($capitaliseFirstChar) {
        $str[0] = strtoupper($str[0]);
    }

    $callback = function($c) {
        return strtoupper($c[1]);
    };

    return preg_replace_callback('/_([a-z])/', $callback, $str);
}

function getClassName($fileContent)
{
    $matchs = [];
    preg_match("/(?:class\s)(\w+)/", $fileContent, $matchs);
    return $matchs[1] ?? null;
}

function makeGetFunction($fieldName)
{
    $fnName = normalizeFieldName($fieldName);
    return "\n\n    public function get" . $fnName . "()\n"
        . "    {\n"
        . "        return \$this->" . $fieldName . ";\n"
        . "    }";
}

function makeSetFunction($fieldName)
{
    $fnName = normalizeFieldName($fieldName);
    $paramName = lcfirst($fnName);
    return "\n\n    public function set" . $fnName . "(\$" . $paramName . ")\n"
        . "    {\n"
        . "        \$this->" . $fieldName . " = \$" . $paramName . ";\n"
        . "        return \$this;\n"
        . "    }";
}

foreach ($files as $file) {
    $fileContent = readFileContent($path . $file);
    $className = getClassName($fileContent);
    // adding use interface
    if (!preg_match('/use\sHTR\\Interfaces\\Entities\\EntityInterface;/', $fileContent)) {
        $search = 'use Doctrine\ORM\Mapping as ORM;';
        $replace = $search . "\nuse HTR\Interfaces\Entities\EntityInterface;";
        $fileContent = str_replace($search, $replace, $fileContent);
    }
    // adding implemets inteface
    if (!preg_match("/class\s{$className}\simplements\sEntityInterface/", $fileContent)) {
        $search = "class {$className}";
        $replace = $search . " implements EntityInterface";
        $fileContent = str_replace($search, $replace, $fileContent);
    }
    $className = "\App\Entities\\" . $className;
    $obj = new $className;
    $setMethods = "";
    $getMothods = "";
    $reflect = new \ReflectionClass($obj);
    $attributes = $reflect->getProperties(\ReflectionProperty::IS_PRIVATE);

    // making setters and getters
    foreach ($attributes as $attribute) {
        $methodName = ucfirst($attribute->name);
        if (!method_exists($obj, 'get' . $methodName)) {
            $getMothods .= makeGetFunction($attribute->name);
        }
        // don't create setId($id) method
        if (!method_exists($obj, 'set' . $methodName) && $attribute->name != 'id') {
            $setMethods .= makeSetFunction($attribute->name);
        }
    }

    // adding setters and getters
    $search = "/\n}\n\n/";
    $replace = $getMothods . $setMethods . "\n}\n";
    $fileContent = preg_replace($search, $replace, $fileContent);
    $fileContent = preg_replace("/\n{3,}/", "\n\n", $fileContent);

    // save modifications into the file
    if (changeFile($path . $file, $fileContent)) {
        echo "Arquivo " . $path . $file . " processado\n";
    } else {
        echo "[ERROR] Arquivo " . $path . $file . " não foi processado\n";
    }
}
echo "Finalizou! =) \n";
