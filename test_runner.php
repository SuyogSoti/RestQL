<?php

namespace RestQL;

use \RestQL\ModelController;
use \RestQL\Request;

include "Request.php";
include "ModelController.php";


$rawJson = <<<EOD
    {
        "query": {"id": 42, "policy": {"id": 54}},
        "features": {"id": true, "policy": {"id": true}},
        "actions": {
            "sort": [
                {
                    "feature": "string",
                    "sortOrderAsc": true
                },
                {
                    "feature": {
                        "policy": "id"
                    },
                    "sortOrderAsc": true
                }
            ]
        }
    }
EOD;

class Policy
{
    public $id;
    public $name;
    public function __construct(int $id)
    {
        $this->id = $id;
        $this->name = "Something Random";
    }
}

class MyModel
{
    public $id;
    public $name;
    public $policies;

    public function __construct(int $id, string $name, $policies = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->policies = $policies;
    }
}

function grabAttribute(string $attr) {
    return function ($model) use ($attr) {
        return $model->$attr;
    };
}

$req = Request::GetRequestFromJson($rawJson);
$featureMap = [
    "filterModel" => function ($parentModel) use ($req) {
        return new MyModel(42, "Tom", [new Policy(54), new Policy(45)]);
    },
    "id" => grabAttribute("id"),
    "policy" => [
        "filterModel" => function ($parentModel) {
            return $parentModel->policies;
        },
        "id" => grabAttribute("id")
    ]
];

$mod = new ModelController($featureMap);
$data = $mod->getModel($req);
echo json_encode($data) . "\n";

