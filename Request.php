<?php

namespace RestQL;

/**
 * This is the structured way to traverse the list that the client has passed in
 */
class Request
{
    public $features;
    public $actions;

    public function __construct($features = [], $actions = [])
    {
        $this->features = $features;
        $this->actions = $actions;
    }

    /**
     * Will create a RestQL\Request from a json string
     *
     * @param string $reqString is the json that the client passes in
     */
    public static function GetRequestFromJson(string $reqString)
    {
        $req = json_decode($reqString, true);
        if (is_null($req["features"]) || is_null($req["actions"])) {
            return null;
        }
        return new Request($req["features"], $req["actions"]);
    }
}
