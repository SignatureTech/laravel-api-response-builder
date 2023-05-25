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

        $this->assertObjectHasAttribute('status', $res);
        $this->assertIsBool(true, $res->status);
        $this->assertObjectHasAttribute('data', $res);
        $this->assertEquals($data['name'], $res->data->name);
        $this->assertEquals($data['email'], $res->data->email);
        $this->assertObjectHasAttribute('message', $res);
        $this->assertEquals($message, $res->message);
    }

    public function test_error(): void
    {
        $message = 'Whoops! Something went wrong please try after some time';
        $code = Response::HTTP_INTERNAL_SERVER_ERROR;

        $res = ResponseBuilder::error($message, $code);

        $this->assertEquals($code, $res->status());

        $res = json_decode($res->getContent());

        $this->assertObjectHasAttribute('status', $res);
        $this->assertIsBool(false, $res->status);
        $this->assertObjectHasAttribute('message', $res);
        $this->assertEquals($message, $res->message);
    }
}
