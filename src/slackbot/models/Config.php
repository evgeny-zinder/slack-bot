<?php

namespace slackbot\models;

use slackbot\util\FileLoader;
use Symfony\Component\Yaml\Parser;
use slackbot\Util;

/**
 * Class Config
 * @package slackbot\models
 */
class Config
{
    /** @var array  */
    private $data = [];

    /** @var Parser */
    private $parser;

    /** @var FileLoader */
    private $loader;

    /**
     * Config constructor.
     * @param Parser $parser
     * @param FileLoader $loader
     */
    public function __construct(Parser $parser, FileLoader $loader) {
        $this->parser = $parser;
        $this->loader = $loader;
    }

    /**
     * @param string $configPath
     */
    public function loadData($configPath) {
        $data = $this->parser->parse(
            $this->loader->load($configPath)
        );
        if (!is_array($data)) {
            throw new \RuntimeException('Error parsing config file');
        }
        $this->data = $data;
    }

    /**
     * Returns array with config top-level section data
     * @param string $section
     * @return array
     */
    public function getSection($section)
    {
        return Util::arrayGet($this->data, $section) ?: [];
    }

    /**
     * Returns entry by full path, ex. "server.name" returns "name" entry from "server" section
     * @param string $entry
     * @return string|null
     */
    public function getEntry($entry)
    {
        list($section, $entry) = explode('.', $entry);
        return Util::arrayGet($this->getSection($section), $entry);
    }
}
