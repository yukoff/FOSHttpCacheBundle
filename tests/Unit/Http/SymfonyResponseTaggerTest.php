<?php

/*
 * This file is part of the FOSHttpCacheBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\HttpCacheBundle\Tests\Unit;

use FOS\HttpCache\ProxyClient\HttpDispatcher;
use FOS\HttpCache\ProxyClient\Invalidation\TagCapable;
use FOS\HttpCache\ProxyClient\ProxyClient;
use FOS\HttpCache\ProxyClient\Varnish;
use FOS\HttpCacheBundle\Http\SymfonyResponseTagger;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;

class SymfonyResponseTaggerTest extends \PHPUnit_Framework_TestCase
{
    private $proxyClient;

    public function setUp()
    {
        $this->proxyClient = \Mockery::mock(ProxyClient::class);
    }

    public function testTagResponse()
    {
        /** @var TagCapable|MockInterface $tag */
        $tag = new Varnish(\Mockery::mock(HttpDispatcher::class));

        $tags1 = ['post-1', 'posts'];
        $tags2 = ['post-2'];
        $tags3 = ['different'];

        $symfonyResponseTagger1 = new SymfonyResponseTagger($tag);
        $response = new Response();
        $response->headers->set($tag->getTagsHeaderName(), '');
        $symfonyResponseTagger1->addTags($tags1);
        $symfonyResponseTagger1->tagSymfonyResponse($response);
        $this->assertTrue($response->headers->has($tag->getTagsHeaderName()));
        $this->assertEquals(implode(',', $tags1), $response->headers->get($tag->getTagsHeaderName()));

        $symfonyResponseTagger2 = new SymfonyResponseTagger($tag);
        $symfonyResponseTagger2->addTags($tags2);
        $symfonyResponseTagger2->tagSymfonyResponse($response);
        $this->assertEquals(implode(',', array_merge($tags2, $tags1)), $response->headers->get($tag->getTagsHeaderName()));

        $symfonyResponseTagger3 = new SymfonyResponseTagger($tag);
        $symfonyResponseTagger3->addTags($tags3);
        $symfonyResponseTagger3->tagSymfonyResponse($response, true);
        $this->assertEquals(implode(',', $tags3), $response->headers->get($tag->getTagsHeaderName()));
    }
}
