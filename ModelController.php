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

        foreach ($req->actions as $action) {

        }

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
        $model = $filter($parentModel);

        foreach ($features as $feature=>$val) {
            if (is_bool($val) && $val) {
                $method = $featureMap[$feature];
                $returnJson[$feature] = $method($model);
            } else {
                $depth[] = $feature;
                $returnJson[$feature] = $this->depthFirstFeatureIteration($val, $depth, $model);
                array_pop($depth);
            }
        }
        return $returnJson;
    }
}
