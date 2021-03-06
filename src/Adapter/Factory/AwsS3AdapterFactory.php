<?php

namespace BsbFlysystem\Adapter\Factory;

use Aws\S3\S3Client;
use BsbFlysystem\Exception\RequirementsException;
use BsbFlysystem\Exception\UnexpectedValueException;
use League\Flysystem\AwsS3v2\AwsS3Adapter as Adapter;
use Zend\ServiceManager\ServiceLocatorInterface;

class AwsS3AdapterFactory extends AbstractAdapterFactory
{

    /**
     * @inheritdoc
     */
    public function doCreateService(ServiceLocatorInterface $serviceLocator)
    {
        if (!class_exists(\League\Flysystem\AwsS3v2\AwsS3Adapter::class)) {
            throw new RequirementsException(
                ['league/flysystem-aws-s3-v2'],
                'AwsS3'
            );
        }

        $client = S3Client::factory([
            'key'    => $this->options['key'],
            'secret' => $this->options['secret'],
            'region' => $this->options['region'],
            'request.options' => $this->options['request.options'],
        ]);

        $adapter = new Adapter($client, $this->options['bucket'], $this->options['prefix']);

        return $adapter;
    }

    /**
     * @inheritdoc
     */
    protected function validateConfig()
    {
        if (!isset($this->options['key'])) {
            throw new UnexpectedValueException("Missing 'key' as option");
        }

        if (!isset($this->options['secret'])) {
            throw new UnexpectedValueException("Missing 'secret' as option");
        }

        if (!isset($this->options['region'])) {
            throw new UnexpectedValueException("Missing 'region' as option");
        }

        if (!isset($this->options['bucket'])) {
            throw new UnexpectedValueException("Missing 'bucket' as option");
        }

        if (!isset($this->options['prefix'])) {
            $this->options['prefix'] = null;
        }

        if (!isset($this->options['request.options'])) {
            $this->options['request.options'] = [];
        }
    }
}
