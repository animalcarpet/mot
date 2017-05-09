<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\Comment;
use DvsaEntities\Entity\Person;
use PHPUnit_Framework_TestCase;

/**
 * Class CommentTest.
 */
class CommentTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $comment = new Comment();

        $this->assertNull($comment->getId());
        $this->assertNull($comment->getComment());
        $this->assertNull($comment->getCommentAuthor());
    }

    public function testSetsPropertiesCorrectly()
    {
        $data = [
            'comment' => 'Invalid address, postcode not real',
            'created' => new \DateTime(),
            'createdBy' => new Person(),
        ];

        $comment = new Comment();

        $comment
            ->setComment($data['comment'])
            ->setCommentAuthor($data['createdBy']);

        $this->assertEquals($data['comment'], $comment->getComment());
        $this->assertEquals($data['createdBy'], $comment->getCommentAuthor());
    }
}
