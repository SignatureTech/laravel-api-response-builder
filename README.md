<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel">
<h1>Laravel API Response Builder</h1>

---

![GitHub](https://img.shields.io/github/license/signaturetech/laravel-api-response-builder)
[![Unit Tests](https://github.com/signaturetech/laravel-api-response-builder/actions/workflows/phpunit.yml/badge.svg)](https://github.com/signaturetech/laravel-api-response-builder/actions/workflows/phpunit.yml)
![Packagist Downloads](https://img.shields.io/packagist/dt/signaturetech/laravel-api-response-builder)

## Table of contents

- [Introduction](#introduction)
- [Installation & Configuration](#installation--configuration)
- [Examples](#examples)
- [License](#license)

## Introduction

`ResponseBuilder` is a [Laravel](https://laravel.com/) package, designed to help you build a nice, normalized and easy to consume
REST API JSON responses.

## Installation & Configuration

You can install this package via composer using:

```
composer require signaturetech/laravel-api-response-builder
```

Next run the command below to setup `api-response.config` file, you can set your configuration.

```
php artisan vendor:publish --tag=response-builder
```

## Examples

### Example : Success

```
use SignatureTech\ResponseBuilder\ResponseBuilder;


public function index() {
    $users = User::query()->take(2)->get();

    return ResponseBuilder::success($users);
}

// Output
Status: 200 OK
{
    "status": true,
    "data": [
        {
            "id": 1,
            "name": "Prof. Bell Hayes",
            "email": "myundt@example.net",
            "email_verified_at": "2022-12-07T05:27:30.000000Z",
            "created_at": "2022-12-07T05:27:30.000000Z",
            "updated_at": "2022-12-07T05:27:30.000000Z"
        },
        {
            "id": 2,
            "name": "Ms. Tessie Streich III",
            "email": "dledner@example.net",
            "email_verified_at": "2022-12-07T05:27:30.000000Z",
            "created_at": "2022-12-07T05:27:30.000000Z",
            "updated_at": "2022-12-07T05:27:30.000000Z"
        }
    ]
}
```

### Example : Custom Success

```
use SignatureTech\ResponseBuilder\ResponseBuilder;


public function login() {
    $user = User::query()->first();
    $token = Uuid::uuid4();

    return ResponseBuilder::asSuccess()
        ->withMessage('Successfuly Login')
        ->with('auth_token', $token)
        ->withData([
            'profile' => new UserResource($user)
        ])
        ->build();
}

// Output
Status: 200 OK
{
    "status": true,
    "message": "Successfuly Login",
    "auth_token": "65050859-1a05-4fa8-827f-7023ff73d4a3",
    "data": {
        "profile": {
            "id": 1,
            "name": "Prof. Bell Hayes",
            "email": "myundt@example.net"
        }
    }
}
```

### Example : Error

```
use SignatureTech\ResponseBuilder\ResponseBuilder;


public function index() {
    return ResponseBuilder::error('Sorry We are not able to getting your details', Response::HTTP_INTERNAL_SERVER_ERROR);
}

// Output
Status: 500 Internal Server Error
{
    "status": false,
    "message": "Sorry We are not able to getting your details"
}
```

### Example : Custom Error

```
use SignatureTech\ResponseBuilder\ResponseBuilder;


public function index() {
    return ResponseBuilder::asError(Response::HTTP_BAD_GATEWAY)
        ->withMessage('Sorry We are not able to getting your details')
        ->with('custom_key', 'value')
        ->withData([
            'bad_gateway_error' => 'We are not able to process your request'
        ])
        ->build();
}

// Output
Status: 502 Bad Gateway
{
    "status": false,
    "message": "Sorry We are not able to getting your details",
    "code": 10001,
    "data": {
        "bad_gateway_error": "We are not able to process your request"
    }
}
```

### Example : Validation Error Message

```
use SignatureTech\ResponseBuilder\Http\ValidationFailed;

class UserRequest extends FormRequest
{
    use ValidationFailed;

    .
    .
}


// Output
Status: 400 Bad Request
{
    "status": false,
    "errors": {
        "name": [
            "The name field is required."
        ],
        "email": [
            "The email field is required."
        ]
    }
}

```

- You can customize HttpStatus Code in `config/api-response.php` under the variable `validation_http_code`.
- You can customize your error message `config/api-response.php` under the variable `show_validation_failed_message`, `all` for get all error messages and `first` for get the first message.

Example:

```
// config/api-response.php
'show_validation_failed_message'  => 'first',

// Output
Status: 400 Bad Request
{
    "status": false,
    "message": "The name field is required."
}
```

### Example : Custom Filed

```
use SignatureTech\ResponseBuilder\ResponseBuilder;

public function index() {
    $user = User::query()->first();

    $token = Uuid::uuid4();

    return ResponseBuilder::asSuccess()->with('auth_token', $token)->withData($user)->build();
}

// Output:
Status: 200 OK
{
    "status": true,
    "auth_token": "21d97007-e2b9-4ee1-86b1-3cfb96787436",
    "data": {
        "id": 1,
        "name": "Prof. Bell Hayes",
        "email": "myundt@example.net",
        "email_verified_at": "2022-12-07T05:27:30.000000Z",
        "created_at": "2022-12-07T05:27:30.000000Z",
        "updated_at": "2022-12-07T05:27:30.000000Z"
    }
}
```

### Example : Pagination

```
use SignatureTech\ResponseBuilder\ResponseBuilder;

public function index() {
    $user = User::query()->paginate(3);

    return ResponseBuilder::asSuccess()->withPagination($user)->build();
}

// Output:
Status: 200 OK
{
    "status": true,
    "data": [
        {
            "id": 1,
            "name": "Prof. Bell Hayes",
            "email": "myundt@example.net",
            "email_verified_at": "2022-12-07T05:27:30.000000Z",
            "created_at": "2022-12-07T05:27:30.000000Z",
            "updated_at": "2022-12-07T05:27:30.000000Z"
        },
        .
        .
    ],
    "meta": {
        "total_page": 14,
        "current_page": 1,
        "total_item": 40,
        "per_page": 3
    },
    "link": {
        "next": true,
        "prev": false
    }
}
```

### Example : Pagination and you are using JsonResource

```
use SignatureTech\ResponseBuilder\ResponseBuilder;

public function index() {
    $user = User::query()->paginate(3);

    return ResponseBuilder::asSuccess()->withPagination($user, UserResource::class)->build();
}

// Output:
Status: 200 OK
{
    "status": true,
    "data": [
        {
            "id": 1,
            "name": "Prof. Bell Hayes",
            "email": "myundt@example.net"
        },
        .
        .
    ],
    "meta": {
        "total_page": 14,
        "current_page": 1,
        "total_item": 40,
        "per_page": 3
    },
    "link": {
        "next": true,
        "prev": false
    }
}
```

## License

- Written and copyrighted &copy;2022 by Prem Chand Saini ([prem@signaturetech.in](mailto:prem@signaturetech.in))
- ResponseBuilder is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
