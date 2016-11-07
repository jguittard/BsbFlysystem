<?php

namespace BsbFlysystemTest\Filter\File;

use BsbFlysystem\Filter\File\RenameUpload;
use BsbFlysystemTest\Framework\TestCase;
use League\Flysystem\AdapterInterface;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

require_once __DIR__ . '/../../Assets/Functions.php';

class RenameUploadTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    protected $filesystem;

    public function setUp()
    {
        $this->filesystem = $this->prophesize('League\Flysystem\FilesystemInterface');
    }

    public function testCanUploadFile()
    {
        $path = 'path/to/file.txt';
        $this->filesystem->putStream($path, Argument::any())
            ->willReturn(true)
            ->shouldBeCalled();
        $this->filesystem->has($path)
            ->willReturn(false);

        $filter = new RenameUpload([
            'target' => $path,
            'filesystem' => $this->filesystem->reveal()
        ]);

        $key = $filter->filter(__DIR__ . '/../../Assets/test.txt');
        $this->assertEquals($path, $key);
    }

    public function testCanUploadFileWhenUploading()
    {
        $path = 'path/to/file.txt';
        $this->filesystem->putStream($path, Argument::any())
            ->willReturn(true)
            ->shouldBeCalled();
        $this->filesystem->has($path)
            ->willReturn(false);

        $filter = new RenameUpload([
            'target' => $path,
            'filesystem' => $this->filesystem->reveal()
        ]);

        $file = [
            'tmp_name' => __DIR__ . '/../../Assets/test.txt',
            'name' => 'test.txt'
        ];
        $temp = $filter->filter($file);

        $this->assertEquals($path, $temp['tmp_name']);
    }

    /*public function testVisibilityIsSetToPrivateByDeault()
    {

    }*/

    public function testCanSetVisibility()
    {
        $path = 'path/to/file.txt';
        $this->filesystem->putStream($path, Argument::any(), Argument::any())
            ->willReturn(true)
            ->shouldBeCalled();
        $this->filesystem->has($path)
            ->willReturn(false);
        /*$this->filesystem->getVisibility($path)
            ->willReturn(true);*/

        $filter = new RenameUpload([
            'target' => $path,
            'filesystem' => $this->filesystem->reveal(),
            'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
        ]);

        $file = [
            'tmp_name' => __DIR__ . '/../../Assets/test.txt',
            'name' => 'test.txt'
        ];

        $filter->filter($file);
        $visibility = $this->filesystem->getVisibility($path);
        var_dump($visibility);exit;
        $this->assertEquals($visibility['visibility'], AdapterInterface::VISIBILITY_PUBLIC);
    }

    public function testWillThrowExceptionWithInvalidConstructorParams()
    {
        $this->setExpectedException('Zend\Filter\Exception\InvalidArgumentException');
        new RenameUpload('something');
    }

    public function testWillThrowExceptionWhenFilesystemNotSet()
    {
        $filter = new RenameUpload([
            'target' => 'path/to/file.txt',
        ]);

        $this->setExpectedException('UnexpectedValueException');
        $filter->filter(__DIR__ . '/../../Assets/test.txt');
    }

    public function testWillThrowExceptionWhenFileIsNotPostUploaded()
    {
        $path = 'path/to/file.txt';
        $this->filesystem->has($path)
            ->willReturn(false);

        $filter = new RenameUpload([
            'target' => $path,
            'filesystem' => $this->filesystem->reveal()
        ]);

        $this->setExpectedException(
            'Zend\Filter\Exception\RuntimeException',
            "File '".__DIR__ . '/../../Assets/Functions.php'."' could not be uploaded. Filter can move only uploaded files."
        );
        $filter->filter(__DIR__ . '/../../Assets/Functions.php');
    }

    public function testWillThrowExceptionWhenFileExists()
    {
        $path = 'path/to/file.txt';
        $this->filesystem->has($path)
            ->willReturn(true)
            ->shouldBeCalled();

        $filter = new RenameUpload([
            'target' => $path,
            'overwrite' => false,
            'filesystem' => $this->filesystem->reveal()
        ]);

        $this->setExpectedException('Zend\Filter\Exception\InvalidArgumentException', "File 'path/to/file.txt' could not be uploaded. It already exists.");
        $filter->filter(__DIR__ . '/../../Assets/test.txt');
    }

    public function testWillThrowExceptionWhenFilesystemFails()
    {
        $path = 'path/to/file.txt';
        $this->filesystem->putStream($path, Argument::any())
            ->willReturn(false)
            ->shouldBeCalled();
        $this->filesystem->has($path)
            ->willReturn(false);

        $filter = new RenameUpload([
            'target' => $path,
            'filesystem' => $this->filesystem->reveal()
        ]);

        $this->setExpectedException(
            'Zend\Filter\Exception\RuntimeException',
            sprintf(
                "File '%s' could not be uploaded. An error occurred while processing the file.",
                __DIR__ . '/../../Assets/test.txt'
            )
        );
        $filter->filter(__DIR__ . '/../../Assets/test.txt');
    }
}
