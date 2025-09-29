<?php

namespace UnitTests\Transform;

use AlibabaCloud\Oss\V2\Models;
use AlibabaCloud\Oss\V2\Transform\BucketWebsite;
use AlibabaCloud\Oss\V2\OperationOutput;
use AlibabaCloud\Oss\V2\Utils;

class BucketWebsiteTest extends \PHPUnit\Framework\TestCase
{
    public function testFromPutBucketWebsite()
    {
        // miss required field
        try {
            $request = new Models\PutBucketWebsiteRequest();
            $input = BucketWebsite::fromPutBucketWebsite($request);
            $this->assertTrue(false, "should not here");
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString("missing required field, bucket", (string)$e);
        }

        try {
            $request = new Models\PutBucketWebsiteRequest('bucket-123');
            $input = BucketWebsite::fromPutBucketWebsite($request);
            $this->assertTrue(false, "should not here");
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString("missing required field, websiteConfiguration", (string)$e);
        }

        // demo1
        $request = new Models\PutBucketWebsiteRequest('bucket-123');
        $request->websiteConfiguration = new Models\WebsiteConfiguration(
            new Models\IndexDocument(
                suffix: 'index.html', supportSubDir: true, type: 0
            ),
            new Models\ErrorDocument(
                key: 'error.html', httpStatus: 404
            ),
            new Models\RoutingRules([
                new Models\RoutingRule(
                    redirect: new Models\RoutingRuleRedirect(
                    redirectType: 'Mirror',
                    mirrorURL: 'http://example.com/',
                    mirrorPassQueryString: true,
                    mirrorCheckMd5: true,
                    mirrorFollowRedirect: true,
                    mirrorHeaders: new Models\MirrorHeaders(
                    passs: ["myheader-key1", "myheader-key2"],
                    sets: array(
                    new Models\MirrorHeaderSet(key: 'myheader-key5', value: 'myheader-value5'),
                ),
                    passAll: true,
                ),
                    passQueryString: true,
                ),
                    ruleNumber: 1,
                    condition: new Models\RoutingRuleCondition(
                        keyPrefixEquals: 'abc/',
                        httpErrorCodeReturnedEquals: 404,
                    )
                ),
                new Models\RoutingRule(
                    redirect: new Models\RoutingRuleRedirect(
                    redirectType: 'AliCDN',
                    protocol: 'http',
                    replaceKeyPrefixWith: 'prefix/${key}.suffix',
                    hostName: 'example.com',
                    passQueryString: false,
                    httpRedirectCode: 301
                ),
                    ruleNumber: 2,
                    condition: new Models\RoutingRuleCondition(
                        keyPrefixEquals: 'abc/',
                        httpErrorCodeReturnedEquals: 404,
                        includeHeaders: [
                            new Models\RoutingRuleIncludeHeader(
                                key: 'host',
                                equals: 'test.oss-cn-beijing-internal.aliyuncs.com',
                            )
                        ]
                    )
                ),
                new Models\RoutingRule(
                    redirect: new Models\RoutingRuleRedirect(
                    redirectType: 'External',
                    protocol: 'http',
                    replaceKeyPrefixWith: 'prefix/${key}',
                    hostName: 'example.com',
                    passQueryString: false,
                    enableReplacePrefix: false,
                    httpRedirectCode: 302,
                ),
                    ruleNumber: 3,
                    condition: new Models\RoutingRuleCondition(
                        httpErrorCodeReturnedEquals: 404,
                    )
                )
            ])
        );
        $input = BucketWebsite::fromPutBucketWebsite($request);
        $this->assertEquals('bucket-123', $input->getBucket());
        $xml = '<?xml version="1.0" encoding="UTF-8"?><WebsiteConfiguration><IndexDocument><Suffix>index.html</Suffix><SupportSubDir>true</SupportSubDir><Type>0</Type></IndexDocument><ErrorDocument><Key>error.html</Key><HttpStatus>404</HttpStatus></ErrorDocument><RoutingRules><RoutingRule><Redirect><RedirectType>Mirror</RedirectType><MirrorURL>http://example.com/</MirrorURL><MirrorPassQueryString>true</MirrorPassQueryString><MirrorCheckMd5>true</MirrorCheckMd5><MirrorFollowRedirect>true</MirrorFollowRedirect><MirrorHeaders><Pass>myheader-key1</Pass><Pass>myheader-key2</Pass><Set><Key>myheader-key5</Key><Value>myheader-value5</Value></Set><PassAll>true</PassAll></MirrorHeaders><PassQueryString>true</PassQueryString></Redirect><RuleNumber>1</RuleNumber><Condition><KeyPrefixEquals>abc/</KeyPrefixEquals><HttpErrorCodeReturnedEquals>404</HttpErrorCodeReturnedEquals></Condition></RoutingRule><RoutingRule><Redirect><RedirectType>AliCDN</RedirectType><Protocol>http</Protocol><ReplaceKeyPrefixWith>prefix/${key}.suffix</ReplaceKeyPrefixWith><HostName>example.com</HostName><PassQueryString>false</PassQueryString><HttpRedirectCode>301</HttpRedirectCode></Redirect><RuleNumber>2</RuleNumber><Condition><KeyPrefixEquals>abc/</KeyPrefixEquals><HttpErrorCodeReturnedEquals>404</HttpErrorCodeReturnedEquals><IncludeHeader><Key>host</Key><Equals>test.oss-cn-beijing-internal.aliyuncs.com</Equals></IncludeHeader></Condition></RoutingRule><RoutingRule><Redirect><RedirectType>External</RedirectType><Protocol>http</Protocol><ReplaceKeyPrefixWith>prefix/${key}</ReplaceKeyPrefixWith><HostName>example.com</HostName><PassQueryString>false</PassQueryString><EnableReplacePrefix>false</EnableReplacePrefix><HttpRedirectCode>302</HttpRedirectCode></Redirect><RuleNumber>3</RuleNumber><Condition><HttpErrorCodeReturnedEquals>404</HttpErrorCodeReturnedEquals></Condition></RoutingRule></RoutingRules></WebsiteConfiguration>';
        $this->assertEquals($xml, $this->cleanXml($input->getBody()->getContents()));

        $request = new Models\PutBucketWebsiteRequest('bucket-123');
        $request->websiteConfiguration = new Models\WebsiteConfiguration(
            new Models\IndexDocument(
                suffix: 'index.html', supportSubDir: true, type: 0
            ),
            new Models\ErrorDocument(
                key: 'error.html', httpStatus: 404
            )
        );
        $input = BucketWebsite::fromPutBucketWebsite($request);
        $this->assertEquals('bucket-123', $input->getBucket());
        $xml = <<<BBB
<?xml version="1.0" encoding="UTF-8"?><WebsiteConfiguration><IndexDocument><Suffix>index.html</Suffix><SupportSubDir>true</SupportSubDir><Type>0</Type></IndexDocument><ErrorDocument><Key>error.html</Key><HttpStatus>404</HttpStatus></ErrorDocument></WebsiteConfiguration>
BBB;
        $this->assertEquals($xml, $this->cleanXml($input->getBody()->getContents()));
    }

    public function testToPutBucketWebsite()
    {
        // empty output
        $output = new OperationOutput();
        $result = BucketWebsite::toPutBucketWebsite($output);
        $this->assertEquals('', $result->status);
        $this->assertEquals(0, $result->statusCode);
        $this->assertEquals('', $result->requestId);
        $this->assertEquals(0, count($result->headers));

        $output = new OperationOutput(
            'OK',
            200,
            ['x-oss-request-id' => '123']
        );
        $result = BucketWebsite::toPutBucketWebsite($output);
        $this->assertEquals('OK', $result->status);
        $this->assertEquals(200, $result->statusCode);
        $this->assertEquals('123', $result->requestId);
        $this->assertEquals(1, count($result->headers));
        $this->assertEquals('123', $result->headers['x-oss-request-id']);
    }

    public function testFromGetBucketWebsite()
    {
        // miss required field
        try {
            $request = new Models\GetBucketWebsiteRequest();
            $input = BucketWebsite::fromGetBucketWebsite($request);
            $this->assertTrue(false, "should not here");
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString("missing required field, bucket", (string)$e);
        }

        $request = new Models\GetBucketWebsiteRequest('bucket-123');
        $input = BucketWebsite::fromGetBucketWebsite($request);
        $this->assertEquals('bucket-123', $input->getBucket());
        $this->assertEquals('1B2M2Y8AsgTpgAmY7PhCfg==', $input->getHeaders()['content-md5']);
    }

    public function testToGetBucketWebsite()
    {
        // empty output
        $output = new OperationOutput();
        $result = BucketWebsite::toGetBucketWebsite($output);
        $this->assertEquals('', $result->status);
        $this->assertEquals(0, $result->statusCode);
        $this->assertEquals('', $result->requestId);
        $this->assertEquals(0, count($result->headers));

        $body = '<?xml version="1.0" encoding="UTF-8"?>
			<WebsiteConfiguration>
				<IndexDocument>
					<Suffix>index.html</Suffix>
				</IndexDocument>
				<ErrorDocument>
				   <Key>error.html</Key>
				   <HttpStatus>404</HttpStatus>
				</ErrorDocument>
			</WebsiteConfiguration>';
        $output = new OperationOutput(
            'OK',
            200,
            ['x-oss-request-id' => '123'],
            Utils::streamFor($body)
        );
        $result = BucketWebsite::toGetBucketWebsite($output);
        $this->assertEquals('OK', $result->status);
        $this->assertEquals(200, $result->statusCode);
        $this->assertEquals('123', $result->requestId);
        $this->assertEquals(1, count($result->headers));
        $this->assertEquals('123', $result->headers['x-oss-request-id']);
        $this->assertEquals('index.html', $result->websiteConfiguration->indexDocument->suffix);
        $this->assertNull($result->websiteConfiguration->indexDocument->supportSubDir);
        $this->assertNull($result->websiteConfiguration->indexDocument->type);
        $this->assertEquals('error.html', $result->websiteConfiguration->errorDocument->key);
        $this->assertEquals('404', $result->websiteConfiguration->errorDocument->httpStatus);

        $body = '<WebsiteConfiguration>
		  <IndexDocument>
			<Suffix>index.html</Suffix>
			<SupportSubDir>true</SupportSubDir>
			<Type>0</Type>
		  </IndexDocument>
		  <ErrorDocument>
			<Key>error.html</Key>
			<HttpStatus>404</HttpStatus>
		  </ErrorDocument>
		  <RoutingRules>
			<RoutingRule>
			  <RuleNumber>1</RuleNumber>
			  <Condition>
				<KeyPrefixEquals>abc/</KeyPrefixEquals>
				<HttpErrorCodeReturnedEquals>404</HttpErrorCodeReturnedEquals>
			  </Condition>
			  <Redirect>
				<RedirectType>Mirror</RedirectType>
				<PassQueryString>true</PassQueryString>
				<MirrorURL>http://example.com/</MirrorURL>   
				<MirrorPassQueryString>true</MirrorPassQueryString>
				<MirrorFollowRedirect>true</MirrorFollowRedirect>
				<MirrorCheckMd5>false</MirrorCheckMd5>
				<MirrorHeaders>
				  <PassAll>true</PassAll>
				  <Pass>myheader-key1</Pass>
				  <Pass>myheader-key2</Pass>
				  <Remove>myheader-key3</Remove>
				  <Remove>myheader-key4</Remove>
				  <Set>
					<Key>myheader-key5</Key>
					<Value>myheader-value5</Value>
				  </Set>
				</MirrorHeaders>
			  </Redirect>
			</RoutingRule>
			<RoutingRule>
			  <RuleNumber>2</RuleNumber>
			  <Condition>
				<KeyPrefixEquals>abc/</KeyPrefixEquals>
				<HttpErrorCodeReturnedEquals>404</HttpErrorCodeReturnedEquals>
				<IncludeHeader>
				  <Key>host</Key>
				  <Equals>test.oss-cn-beijing-internal.aliyuncs.com</Equals>
				</IncludeHeader>
			  </Condition>
			  <Redirect>
				<RedirectType>AliCDN</RedirectType>
				<Protocol>http</Protocol>
				<HostName>example.com</HostName>
				<PassQueryString>false</PassQueryString>
				<ReplaceKeyWith>prefix/${key}.suffix</ReplaceKeyWith>
				<HttpRedirectCode>301</HttpRedirectCode>
			  </Redirect>
			</RoutingRule>
			<RoutingRule>
			  <Condition>
				<HttpErrorCodeReturnedEquals>404</HttpErrorCodeReturnedEquals>
			  </Condition>
			  <RuleNumber>3</RuleNumber>
			  <Redirect>
				<ReplaceKeyWith>prefix/${key}</ReplaceKeyWith>
				<HttpRedirectCode>302</HttpRedirectCode>
				<EnableReplacePrefix>false</EnableReplacePrefix>
				<PassQueryString>false</PassQueryString>
				<Protocol>http</Protocol>
				<HostName>example.com</HostName>
				<RedirectType>External</RedirectType>
			  </Redirect>
			</RoutingRule>
		  </RoutingRules>
		</WebsiteConfiguration>';
        $output = new OperationOutput(
            'OK',
            200,
            ['x-oss-request-id' => '123'],
            Utils::streamFor($body)
        );
        $result = BucketWebsite::toGetBucketWebsite($output);
        $this->assertEquals('OK', $result->status);
        $this->assertEquals(200, $result->statusCode);
        $this->assertEquals('123', $result->requestId);
        $this->assertEquals(1, count($result->headers));
        $this->assertEquals('123', $result->headers['x-oss-request-id']);
        $this->assertEquals('index.html', $result->websiteConfiguration->indexDocument->suffix);
        $this->assertTrue($result->websiteConfiguration->indexDocument->supportSubDir);
        $this->assertEquals(0, $result->websiteConfiguration->indexDocument->type);
        $this->assertEquals('error.html', $result->websiteConfiguration->errorDocument->key);
        $this->assertEquals('404', $result->websiteConfiguration->errorDocument->httpStatus);
        $this->assertEquals(3, count($result->websiteConfiguration->routingRules->routingRules));
        $this->assertEquals(1, $result->websiteConfiguration->routingRules->routingRules[0]->ruleNumber);
        $this->assertEquals('abc/', $result->websiteConfiguration->routingRules->routingRules[0]->condition->keyPrefixEquals);
        $this->assertEquals(404, $result->websiteConfiguration->routingRules->routingRules[0]->condition->httpErrorCodeReturnedEquals);
        $this->assertEquals('Mirror', $result->websiteConfiguration->routingRules->routingRules[0]->redirect->redirectType);
        $this->assertTrue($result->websiteConfiguration->routingRules->routingRules[0]->redirect->passQueryString);
        $this->assertEquals('http://example.com/', $result->websiteConfiguration->routingRules->routingRules[0]->redirect->mirrorURL);
        $this->assertTrue($result->websiteConfiguration->routingRules->routingRules[0]->redirect->mirrorPassQueryString);
        $this->assertTrue($result->websiteConfiguration->routingRules->routingRules[0]->redirect->mirrorFollowRedirect);
        $this->assertFalse($result->websiteConfiguration->routingRules->routingRules[0]->redirect->mirrorCheckMd5);
        $this->assertTrue($result->websiteConfiguration->routingRules->routingRules[0]->redirect->mirrorHeaders->passAll);
        $this->assertEquals('myheader-key1', $result->websiteConfiguration->routingRules->routingRules[0]->redirect->mirrorHeaders->passs[0]);
        $this->assertEquals('myheader-key2', $result->websiteConfiguration->routingRules->routingRules[0]->redirect->mirrorHeaders->passs[1]);
        $this->assertEquals('myheader-key3', $result->websiteConfiguration->routingRules->routingRules[0]->redirect->mirrorHeaders->removes[0]);
        $this->assertEquals('myheader-key4', $result->websiteConfiguration->routingRules->routingRules[0]->redirect->mirrorHeaders->removes[1]);
        $this->assertEquals('myheader-key5', $result->websiteConfiguration->routingRules->routingRules[0]->redirect->mirrorHeaders->sets[0]->key);
        $this->assertEquals('myheader-value5', $result->websiteConfiguration->routingRules->routingRules[0]->redirect->mirrorHeaders->sets[0]->value);

        $this->assertEquals(2, $result->websiteConfiguration->routingRules->routingRules[1]->ruleNumber);
        $this->assertEquals('abc/', $result->websiteConfiguration->routingRules->routingRules[1]->condition->keyPrefixEquals);
        $this->assertEquals(404, $result->websiteConfiguration->routingRules->routingRules[1]->condition->httpErrorCodeReturnedEquals);
        $this->assertEquals('host', $result->websiteConfiguration->routingRules->routingRules[1]->condition->includeHeaders[0]->key);
        $this->assertEquals('test.oss-cn-beijing-internal.aliyuncs.com', $result->websiteConfiguration->routingRules->routingRules[1]->condition->includeHeaders[0]->equals);
        $this->assertEquals('AliCDN', $result->websiteConfiguration->routingRules->routingRules[1]->redirect->redirectType);
        $this->assertEquals('http', $result->websiteConfiguration->routingRules->routingRules[1]->redirect->protocol);
        $this->assertFalse($result->websiteConfiguration->routingRules->routingRules[1]->redirect->passQueryString);
        $this->assertEquals('prefix/${key}.suffix', $result->websiteConfiguration->routingRules->routingRules[1]->redirect->replaceKeyWith);
        $this->assertEquals(301, $result->websiteConfiguration->routingRules->routingRules[1]->redirect->httpRedirectCode);

        $this->assertEquals(3, $result->websiteConfiguration->routingRules->routingRules[2]->ruleNumber);
        $this->assertEquals('External', $result->websiteConfiguration->routingRules->routingRules[2]->redirect->redirectType);
        $this->assertFalse($result->websiteConfiguration->routingRules->routingRules[2]->redirect->passQueryString);
        $this->assertEquals('prefix/${key}', $result->websiteConfiguration->routingRules->routingRules[2]->redirect->replaceKeyWith);
        $this->assertEquals(302, $result->websiteConfiguration->routingRules->routingRules[2]->redirect->httpRedirectCode);
        $this->assertFalse($result->websiteConfiguration->routingRules->routingRules[2]->redirect->enableReplacePrefix);
        $this->assertEquals('http', $result->websiteConfiguration->routingRules->routingRules[2]->redirect->protocol);
        $this->assertEquals('example.com', $result->websiteConfiguration->routingRules->routingRules[2]->redirect->hostName);
    }

    public function testFromDeleteBucketWebsite()
    {
        // miss required field
        try {
            $request = new Models\DeleteBucketWebsiteRequest();
            $input = BucketWebsite::fromDeleteBucketWebsite($request);
            $this->assertTrue(false, "should not here");
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString("missing required field, bucket", (string)$e);
        }

        $request = new Models\DeleteBucketWebsiteRequest('bucket-123');
        $input = BucketWebsite::fromDeleteBucketWebsite($request);
        $this->assertEquals('bucket-123', $input->getBucket());
        $this->assertEquals('1B2M2Y8AsgTpgAmY7PhCfg==', $input->getHeaders()['content-md5']);
    }

    public function testToDeleteBucketWebsite()
    {
        // empty output
        $output = new OperationOutput();
        $result = BucketWebsite::toDeleteBucketWebsite($output);
        $this->assertEquals('', $result->status);
        $this->assertEquals(0, $result->statusCode);
        $this->assertEquals('', $result->requestId);
        $this->assertEquals(0, count($result->headers));

        $output = new OperationOutput(
            'No Content',
            204,
            ['x-oss-request-id' => '123']
        );
        $result = BucketWebsite::toDeleteBucketWebsite($output);
        $this->assertEquals('No Content', $result->status);
        $this->assertEquals(204, $result->statusCode);
        $this->assertEquals('123', $result->requestId);
        $this->assertEquals(1, count($result->headers));
        $this->assertEquals('123', $result->headers['x-oss-request-id']);
    }

    private function cleanXml($xml): array|string
    {
        return str_replace("\n", "", str_replace("\r", "", $xml));
    }
}
