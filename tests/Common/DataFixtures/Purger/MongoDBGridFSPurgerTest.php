<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\DataFixtures\Purger;

use Doctrine\Common\DataFixtures\Purger\MongoDBPurgeMode;
use Doctrine\ODM\MongoDB\Repository\GridFSRepository;
use Doctrine\Tests\Common\DataFixtures\TestDocument\Image;

use function base64_encode;
use function fclose;
use function fopen;

class MongoDBGridFSPurgerTest extends BaseMongoDBPurgerTestCase
{
    public const TEST_DOCUMENT_IMAGE = Image::class;

    public function testPurgeWithDelete(): void
    {
        $purger = $this->getPurger();
        $dm     = $purger->getObjectManager();
        $purger->setPurgeMode(MongoDBPurgeMode::Delete);

        self::assertSame(MongoDBPurgeMode::Delete, $purger->getPurgeMode());

        $filesCollection  = $dm->getDocumentBucket(self::TEST_DOCUMENT_IMAGE)->getFilesCollection();
        $chunksCollection = $dm->getDocumentBucket(self::TEST_DOCUMENT_IMAGE)->getChunksCollection();

        $filesCollection->drop();
        $chunksCollection->drop();

        $this->assertIndexCount(0, $filesCollection);
        $this->assertIndexCount(0, $chunksCollection);

        $purger->purge();

        // Collection isn't created yet, so no indices should be present
        $this->assertIndexCount(0, $filesCollection);
        $this->assertIndexCount(0, $chunksCollection);

        // Create a data stream for GridFS
        $stream = fopen('data://text/plain;base64,' . base64_encode('test content'), 'r');
        self::assertIsResource($stream);

        /** @var GridFSRepository<Image> $repository */
        $repository = $dm->getRepository(self::TEST_DOCUMENT_IMAGE);
        $repository->uploadFromStream('test.jpg', $stream);

        fclose($stream);

        $this->assertSame(1, $filesCollection->countDocuments());
        $this->assertSame(1, $chunksCollection->countDocuments());

        $this->assertIndexCount(2, $filesCollection);
        $this->assertIndexCount(2, $chunksCollection);

        $purger->purge();

        // After purging, the collection should still exist, but the file should be deleted
        $this->assertIndexCount(2, $filesCollection);
        $this->assertIndexCount(2, $chunksCollection);
        $this->assertSame(0, $filesCollection->countDocuments());
        $this->assertSame(0, $chunksCollection->countDocuments());
    }

    public function testPurgeKeepsIndices(): void
    {
        $purger = $this->getPurger();
        $dm     = $purger->getObjectManager();

        $filesCollection  = $dm->getDocumentBucket(self::TEST_DOCUMENT_IMAGE)->getFilesCollection();
        $chunksCollection = $dm->getDocumentBucket(self::TEST_DOCUMENT_IMAGE)->getChunksCollection();

        $filesCollection->drop();
        $chunksCollection->drop();

        $this->assertIndexCount(0, $filesCollection);
        $this->assertIndexCount(0, $chunksCollection);

        // Create a data stream for GridFS
        $stream = fopen('data://text/plain;base64,' . base64_encode('test content'), 'r');
        self::assertIsResource($stream);

        // Upload file using repository method
        /** @var GridFSRepository<Image> $repository */
        $repository = $dm->getRepository(self::TEST_DOCUMENT_IMAGE);
        $repository->uploadFromStream('test.jpg', $stream);

        fclose($stream);

        $dm->getSchemaManager()->ensureDocumentIndexes(self::TEST_DOCUMENT_IMAGE);

        $this->assertIndexCount(2, $filesCollection);
        $this->assertIndexCount(2, $chunksCollection);

        $this->assertSame(1, $filesCollection->countDocuments());
        $this->assertSame(1, $chunksCollection->countDocuments());

        $purger->purge();

        $this->assertIndexCount(2, $filesCollection);
        $this->assertIndexCount(2, $chunksCollection);

        $this->assertSame(0, $filesCollection->countDocuments());
        $this->assertSame(0, $chunksCollection->countDocuments());
    }
}
