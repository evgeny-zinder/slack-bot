<?php

namespace slackbot\models;

use slackbot\util\FileLoader;
use Symfony\Component\Yaml\Parser;
use eznio\ar\Ar;

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
    public function __construct(Parser $parser, FileLoader $loader)
    {
        $this->parser = $parser;
        $this->loader = $loader;
    }

    /**
     * @param string $configPath
     */
    public function loadData($configPath)
    {
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
        return Ar::get($this->data, $section) ?: [];
    }

    /**
     * Returns entry by full path, ex. "server.name" returns "name" entry from "server" section
     * @param string $entry
     * @return mixed
     */
    public function getEntry($entry)
    {
        if (false === strpos($entry, '.')) {
            return $this->getSection($entry);
        }
        list($section, $entry) = explode('.', $entry, 2);
        return Ar::get($this->getSection($section), $entry);
    }

    /**
     * Returns array unnamed section bu its subkey
     * @param string $path
     * @param string $searchCriteria
     * @return array
     */
    public function getSectionFromArray($path, $searchCriteria)
    {
        $data = $this->getEntry($path);
        if (!is_array($data)) {
            return [];
        }
        $searchCriteriaData = explode('=', $searchCriteria);
        $fieldName = Ar::get($searchCriteriaData, 0);
        $fieldValue = Ar::get($searchCriteriaData, 1);
        if (null === $fieldName || null === $fieldValue) {
            throw new \LogicException('Invalid config entry search criteria');
        }
        foreach ($data as $item) {
            if (Ar::get($item, $fieldName) === $fieldValue) {
                return $item;
            }
        }
        return [];
    }
}
