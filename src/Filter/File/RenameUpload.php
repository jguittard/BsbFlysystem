<?php

namespace BsbFlysystem\Filter\File;

use League\Flysystem\AdapterInterface;
use League\Flysystem\FilesystemInterface;
use UnexpectedValueException;
use Zend\Filter\File\RenameUpload as RenameUploadFilter;
use Zend\Filter\Exception;

class RenameUpload extends RenameUploadFilter
{
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $visibility;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options)
    {
        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '"%s" expects an array; received "%s"',
                __METHOD__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }
        parent::__construct($options);
    }

    /**
     * @throws UnexpectedValueException
     * @return FilesystemInterface
     */
    public function getFilesystem()
    {
        if (!$this->filesystem) {
            throw new UnexpectedValueException('Missing required filesystem.');
        }

        return $this->filesystem;
    }

    /**
     * @param FilesystemInterface $filesystem
     * @return RenameUpload
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
        return $this;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     * @return RenameUpload
     */
    public function setVisibility($visibility)
    {
        if (!in_array($visibility, [
            AdapterInterface::VISIBILITY_PUBLIC,
            AdapterInterface::VISIBILITY_PRIVATE])
        ) {
            throw new UnexpectedValueException('Unknown visibility');
        }
        $this->visibility = $visibility;
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function getFinalTarget($uploadData)
    {
        return trim(str_replace('\\', '/', parent::getFinalTarget($uploadData)), '/');
    }

    /**
     * @inheritdoc
     */
    protected function checkFileExists($targetFile)
    {
        if (!$this->getOverwrite() && $this->getFilesystem()->has($targetFile)) {
            throw new Exception\InvalidArgumentException(
                sprintf("File '%s' could not be uploaded. It already exists.", $targetFile)
            );
        }
    }

    /**
     * @inheritdoc
     */
    protected function moveUploadedFile($sourceFile, $targetFile)
    {
        if (!is_uploaded_file($sourceFile)) {
            throw new Exception\RuntimeException(
                sprintf("File '%s' could not be uploaded. Filter can move only uploaded files.", $sourceFile),
                0
            );
        }
        $stream = fopen($sourceFile, 'r+');

        if ($this->getVisibility()) {
            $result = $this->getFilesystem()->putStream($targetFile, $stream, ['visibility' => $this->getVisibility()]);
        } else {
            $result = $this->getFilesystem()->putStream($targetFile, $stream);
        }


        fclose($stream);

        if (!$result) {
            throw new Exception\RuntimeException(
                sprintf("File '%s' could not be uploaded. An error occurred while processing the file.", $sourceFile),
                0
            );
        }

        return $result;
    }
}
