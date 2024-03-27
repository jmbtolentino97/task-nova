<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;
use ErrorException;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use ReflectionClass;
use ReflectionMethod;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $defaultLimit = 1000;

    public function index(Request $request)
    {
        return $this->getModelQueryBuilder()
            ->paginate((int)$request->query('page_size', $this->defaultLimit));
    }

    public function store(Request $request)
    {
        $data = $request->only($this->getFillable());
        $result = $this->getModelInstance();
        $result->fill($data)->save();
        return response(
            $this
                ->getModelQueryBuilder(
                    $this->getModelInstance()
                        ->where($result->getKeyName(), $result->toArray()[$result->getKeyName()]))
                ->first(),
            Response::HTTP_CREATED);
    }

    public function show(Request $request, $id)
    {
        $model = $this->getModelInstance();
        return response(
            $this
                ->getModelQueryBuilder(
                    $model
                        ->where($model->getKeyName(), $id))
                ->first(),
            Response::HTTP_OK);
    }

    /**
     * TODOS:
     * - Force fill update mode
     */
    public function update(Request $request, $id)
    {
        $model = $this->getModelInstance();
        $primaryKey = $model->getKeyName();
        $data = $request->only($this->getFillable());
        $result = $this->getModelQueryBuilder(
            $model->where($primaryKey, $id)
        )->get()[0];
        $result->fill($data)->save();
        return response(
            $this
                ->getModelQueryBuilder(
                    $this->getModelInstance()
                        ->where($primaryKey, $result->getKey()))
                ->first(),
            Response::HTTP_OK);
    }

    public function destroy(Request $request, $id)
    {
        $resource = $this->getModelInstance()->find($id);
        if(!empty($resource)) 
        {
            $resource->delete();
        }

        return response()->noContent();
    }

    protected function getModelQueryBuilder($model = null) : QueryBuilder
    {
        $result = QueryBuilder::
            for($model != null ? $model : $this->getModelInstance())
            ->allowedFields($this->getAllowedFields())
            ->allowedIncludes($this->getAllowedIncludes())
            ->allowedSorts($this->getAllowedSorts())
            ->allowedFilters($this->getAllowedFilters());

        return $result;
    }

    protected function getAllowedFields() : array
    {
        $model = $this->getModelInstance();
        return array_map(function($column) {
            return $column;
        }, array_diff(Schema::getColumnListing($model->getTable()), $model->getHidden()));
    }

    protected function getAllowedFilters() : array
    {
        $result = array_map(function($column) {
            return AllowedFilter::exact($column);
        }, Schema::getColumnListing($this->getModelInstance()->getTable()));
        $result = array_merge($result, array(AllowedFilter::trashed()));

        return $result;
    }

    protected function getAllowedSorts() : array
    {
        return array_map(function($column) {
            return $column;
        }, Schema::getColumnListing($this->getModelInstance()->getTable()));
    }

    protected function getAllowedIncludes() : array
    {
        return array_map(function($relation) {
            return AllowedInclude::relationship($relation['name']);
        }, $this->getModelRelations());
    }

    protected function getFillable()
    {
        $model = $this->getModelInstance();
        return 
            empty($model->getFillable())
                ? array_diff(Schema::getColumnListing($model->getTable()), $model->getGuarded())
                : $model->getFillable();
    }

    abstract protected function getModelInstance() : Model;

    protected function getModelRelations() {

        $model = $this->getModelInstance()->replicate();

        $relationships = [];

        $modelFilePath = (new ReflectionClass($model))->getFileName();
        foreach((new ReflectionClass($model))->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
        {
            if ($method->class != get_class($model) ||
                $method->getFileName() != $modelFilePath ||
                !empty($method->getParameters()) ||
                $method->getName() == __FUNCTION__) {
                continue;
            }

            try {
                $return = $method->invoke($model);

                if ($return instanceof Relation) {
                    $relationships[] = [
                        'name' => $method->getName(),
                        'type' => (new ReflectionClass($return))->getShortName(),
                        'model' => (new ReflectionClass($return->getRelated()))->getName()
                    ];
                }
            } catch(ErrorException $e) {}
        }

        return $relationships;
    }
}
