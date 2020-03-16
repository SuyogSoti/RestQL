# RestQL

A structured QL for exposing REST APIs.

## The Problem

The current problem is that when REST APIs expose models, they send everything
about models when the clients may not need everything about the models. Some
operations that the client does not need may be specially costly which is
being performed anyway.


## Current Solutions

The current solutions are two fold. The first is to create a different end
point for more light weight operations. The problem with this method is again
that the API does not know exactly what the clients want, and the clients
could all have different needs.

The second solution is to use GraphQL. GraphQL is a new way of exposing APIs
that only computes what the client needs. The problem with GraphQL is also two
fold. The first problem is that the client has no good way for asking the
server to perform predetermined actions like sorting. This becomes specially
important as the APIs return data in a paginated way. The second problem is
that there are projects already built on top of the REST API, and it is too
much work to translate it to a new framework.


## RestQL is the Solution

RestQL is a API language for a REST API. There is a spec for defining how the
model should be filtered as well as what features of the model should be
returned. In addition, a lot of consideration was put into figuring out how to
let the server expose actions like sort to the clients. Based on those
requirements, the following preliminary specs are shown below.


### The Client

What is shown below is the request that the client would send to the server.
The query field lets the server know what to filter. If a model has many policies (a
different model), then the syntax is defined there. The feature syntax is
what ought to be returned. That syntax is very similar to the query syntax.
The actions define the possible different actions that the server could do
before sending the results back. I think these specs are fairly self-explanatory.

```json
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
```

### The Server

The server developers would have a little bit of work to do. If the client
submits the json above as the request, the client would have the following
work to do.

1.  Convert the Json to a RestQL Request Object
1.  Create a feature map which is described below
1.  Pass that into the model controller

```php
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

// a helper function for the feature map
function grabAttribute(string $attr) {
    return function ($model) use ($attr) {
        return $model->$attr;
    };
}

// Convert the Json to a RestQL Request Object
$req = Request::GetRequestFromJson($rawJson);


// The actual feature map
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
$data = $mod->getModel($req); // returns the exact data that the client wants
echo json_encode($data) . "\n";
```

As it can be seen above, the feature map is where most of the work comes in
for the developers. The feature map has a few components to it.


#### The `filterModel`

The `filterModel` is keyword that can not be a feature. The value of
`filterModel` is called with the `parentModel` of the tree passed in. At the
root, `parentModel` is `null`. Afterwards, every feature defined, has its
value called with the model from `filterModel` passed in as the parameter.
Using Clojures this process can be largely automated. The tricky part comes
in when we look are querying for the models in `filterModel`. The
`filterModel` is where the querying and the actions will take place. When
fetching, the developer will have access to the request so they will know
exactly what fields to fetch from the database.

It is to be noted that the function for each feature will only be executed
if the client requests for the feature but the functions for all
`filterModel` will be executed.

It is also to be noted that `filterModel` may return either an array or a
object. If it returns an array, the feature map will be applied to each
element in that array.


## Future

As it can be seen, I have just defined the initial specs. This repo is to
mainly define the specs and show how it can be implemented in one language. I
am sorry that the first language is PHP.
