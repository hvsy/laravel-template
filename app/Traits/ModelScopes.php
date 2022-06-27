<?php
/**
 * Created by PhpStorm.
 * User: David
 * Date: 2018/4/2
 * Time: 16:29
 */

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;


/**
 * Trait CommonScope 公共作用域片段
 * @package App\Traits
 * @method static Builder|static random($limit = 0) 随机排序
 * @method static Builder|static latest()   最新的在上面
 * @method static Builder|static oldest()   最老的在上面
 */
trait ModelScopes
{
    public function scopeRandom($query, $limit = 0): void{
        $query->orderBy(\DB::Raw('rand()'));
        if ($limit > 0) {
            $query->limit(1);
        }
    }

    public function scopeLatest($query): void{
        $query->orderBy('created_at', 'desc');
    }

    public function scopeOldest($query): void{
        $query->orderBy('created_at', 'asc');
    }



}
