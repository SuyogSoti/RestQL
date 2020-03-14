<?php

namespace RestQL;

use \RestQL\Request;

class ModelController {
    private $featureMap = [];

    function __construct($featureMap = [])
    {
        $this->featureMap = $featureMap;
    }

    public function getModel(Request $req)
    {
        $data = $this->depthFirstFeatureIteration($req->features);
        return $data;
    }

    private function depthFirstFeatureIteration($features = [], $depth = [], $parentModel = null)
    {
        $returnJson = [];
        $featureMap = $this->featureMap;

        foreach ($depth as $d) {
            $featureMap = $featureMap[$d];
        }

        $filter = $featureMap['filterModel'];
        $models = $filter($parentModel);
        if (!is_array($models)) {
            $models = [$models];
        }
        foreach ($models as $model) {
            $modelJson = [];
            foreach ($features as $feature=>$val) {
                if (is_bool($val) && $val) {
                    $method = $featureMap[$feature];
                    $modelJson[$feature] = $method($model);
                } else {
                    $depth[] = $feature;
                    $modelJson[$feature] = $this->depthFirstFeatureIteration($val, $depth, $model);
                    array_pop($depth);
                }
            }
            $returnJson[] = $modelJson;
        }
        return $returnJson;
    }
}
