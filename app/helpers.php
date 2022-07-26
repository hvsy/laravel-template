<?php

use App\Custom\ResponseHelper;

if(!function_exists('db_log')){
    function db_log(\Closure $callback): array{
        \DB::connection()->enableQueryLog();
        try{
            $callback();
        }catch(\Exception $exception){
            throw $exception;
        }finally{
            return \DB::getQueryLog();
//            \DB::bindValues(\DB::statement($query), $bindings);
//            return collect(\DB::getQueryLog())->map(function($item){
//                $statement = \DB::getPdo()->prepare($item['query']);
//                \DB::bindValues($statement, $item['bindings']);
//            })->toArray();
        }
    }
}


if(!function_exists('res')){
    function res(): ResponseHelper{
        return new ResponseHelper();
    }
}

if(!function_exists('enum_random')){
    function enum_random($class){
        $cases = $class::cases();
        $idx = array_rand($cases,1);
        return $cases[$idx];
    }
}

if(!function_exists('ls')){
    function ls(string $target,bool $absolute = false): array{
        $dir = new DirectoryIterator($target);
        $list = [];
        /**
         * @var \SplFileInfo $info
         */
        foreach(new IteratorIterator($dir) as $info){
            if($info->isFile()){
                $path = $info->getRealPath();
                $list[] = $absolute ? $path : str_replace($target . '/', '', $path);
            }
        }
        return $list;
    }
}
