<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait Searchable{
    public function scopeSearch(Builder $query,array $columns,string $key,string $glue = ' '): void{
          $keys = \explode($glue, $key);
          $query->where(function ($query) use ($keys, $columns) {
              foreach ($columns as $column) {
                  $fields = explode(".", $column);
                  if (count($fields) > 1) {
                      $field = array_pop($fields);
                      $query->orWhereHas(implode('.',$fields,), function ($query) use ($keys, $field) {
                          $query->where(function ($query) use ($keys, $field) {
                              foreach ($keys as $key) {
                                  $query->orWhere($field, 'like', "%{$key}%");
                              }
                          });
                      });
                  } else {
                      foreach ($keys as $key) {
                          $query->orWhere($column, 'like', "%{$key}%");
                      }
                  }
              }
          });
      }

      /**
       * @param Builder $query
       * @param array $filters
       */
      public function scopeFilters(Builder $query,array $filters = []): void{
          foreach ($filters as $filter => $values) {
              $fields = \explode('.', $filter,2);
              $fieldName = $fields[0];
//              dd($fields);
              if (count($fields) > 1) {
                  $query->whereHas($fieldName, function ($query) use ($fields, $values) {
                      $query->whereIn($fields[1], $values);
                  });
              } else {
                  $method = $this->getMethod($query, $fieldName, 'filter');
                  if($method){
                      $query->{$method}(...$values);
                  }else{
                      $query->whereIn($fieldName, $values);
                  }
              }
          }
      }

      /**
       * @param Builder $query
       * @param string  $fieldName
       * @param string  $suffix
       * @return null|string
       */
      public function getMethod(Builder $query,string $fieldName,string $suffix): ?string{
          $field = Str::studly($fieldName);
          $methodName = "{$field}$suffix";
          if(method_exists($query->getModel(),"scope".$methodName)){
              return $methodName;
          }
          return null;
      }

      public function scopeDateFilters($query, $dates): void{
          foreach ($dates as $field => $date) {
              $query->where($field, '>', $date[0] . ' 00:00:00');
              if (count($date) > 1) {
                  $query->where($field, '<=', $date[1] . " 23:59:59");
              }
          }
      }

      public function scopeList($query, $request, $search = []): void{

          $filters = $request->get('filters', null);

          $query->when($filters, function ($query) use ($filters) {
              $query->filters($filters);
          });
          $keyword = $request->get('keyword', null);

          $query->when($keyword, function ($query) use ($keyword, $search) {
              $query->search($search, $keyword);
          });

          $order = $request->get('order', null);
          $dates = $request->get('date_filters', false);
          $query->when($dates, function ($query) use ($dates) {
              $query->dateFilters($dates);
          });

          $query->when($order, function ($query) use ($order) {
              $query->orderBy($order[0], substr($order[1] ?? 'asc',0,3));
          });
      }
}
