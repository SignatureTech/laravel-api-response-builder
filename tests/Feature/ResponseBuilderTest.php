<?php

namespace SignatureTech\ResponseBuilder\Feature;

use Illuminate\Http\Response;
use SignatureTech\ResponseBuilder\ResponseBuilder;
use SignatureTech\ResponseBuilder\Tests\TestCase;

class ResponseBuilderTest extends TestCase
{
    public function test_success(): void
    {
        $httpOk = Response::HTTP_OK;

        $data = [
            'name' => 'Prem Chand Saini',
            'email' => 'premchandsaini779@gmail.com'
        ];

        $message = 'Getting the user details';

        $res = ResponseBuilder::success($data, $message);

        $this->assertEquals($httpOk, $res->status());

        $res = json_decode($res->getContent());

        $this->assertIsBool(true, $res->status);
        $this->assertEquals($data['name'], $res->data->name);
        $this->assertEquals($data['email'], $res->data->email);
        $this->assertEquals($message, $res->message);
    }

    public function test_error(): void
    {
        $message = 'Whoops! Something went wrong please try after some time';
        $code = Response::HTTP_INTERNAL_SERVER_ERROR;

        $res = ResponseBuilder::error($message, $code);

        $this->assertEquals($code, $res->status());

        $res = json_decode($res->getContent());

        $this->assertIsBool(false, $res->status);
        $this->assertEquals($message, $res->message);
    }

    public function test_success_with_additional_data(): void
    {
        $httpOk = Response::HTTP_OK;

        $data = [
            'name' => 'Prem Chand Saini',
            'email' => 'premchandsaini779@gmail.com'
        ];

        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c";

        $message = 'Getting the user details';

        $res = ResponseBuilder::asSuccess()
            ->withMessage($message)
            ->with('auth_token', $token)
            ->withData($data)
            ->build();;

        $this->assertEquals($httpOk, $res->status());

        $res = json_decode($res->getContent());

        $this->assertIsBool(true, $res->status);
        $this->assertEquals($data['name'], $res->data->name);
        $this->assertEquals($data['email'], $res->data->email);
        $this->assertEquals($token, $res->auth_token);
        $this->assertEquals($message, $res->message);
    }
}
