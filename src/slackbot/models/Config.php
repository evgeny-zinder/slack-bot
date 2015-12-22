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
     * @return null
     */
    public function getEntry($entry)
    {
        list($section, $entry) = explode('.', $entry);
        return Util::arrayGet($this->getSection($section), $entry);
    }

    /**
     * Looks for entry in unnamed array by some criteria
     * @param string $section section path
     * @param string $searchCriteria
     * @return null
     */
    public function getSectionFromArray($section, $searchCriteria) {
        $data = $this->getSection($section);
        if (!is_array($data)) {
            return null;
        }
        $searchCriteriaData = explode('=', $searchCriteria);
        $fieldName = Util::arrayGet($searchCriteriaData, 0);
        $fieldValue = Util::arrayGet($searchCriteriaData, 1);
        if (null === $fieldName || null === $fieldValue) {
            throw new \LogicException('Invalid config entry search criteria');
        }
        foreach ($data as $item) {
            if ($fieldValue === Util::arrayGet($item, $fieldName)) {
                return $item;
            }
        }
        return null;
    }
}
