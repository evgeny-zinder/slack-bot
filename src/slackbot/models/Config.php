<?php

namespace slackbot\models;

use slackbot\util\FileLoader;
use Symfony\Component\Yaml\Parser;
use slackbot\Util;

class Config
{
    private $data = [];

    /** @var Parser */
    private $parser;

    /** @var FileLoader */
    private $loader;

    public function __construct(Parser $parser, FileLoader $loader) {
        $this->parser = $parser;
        $this->loader = $loader;
    }

    public function loadData($configPath) {
        $data = $this->parser->parse(
            $this->loader->load($configPath)
        );
        if (!is_array($data)) {
            throw new \RuntimeException('Error parsing config file');
        }
        $this->data = $data;
    }

    public function getSection($section)
    {
        return Util::arrayGet($this->data, $section) ?: [];
    }

    public function getEntry($entry)
    {
        list($section, $entry) = explode('.', $entry);
        return Util::arrayGet($this->getSection($section), $entry);
    }

    public function getSectionFromArray($section, $searchCriteria) {
        $data = $this->getSection($section);
        if (!is_array($data)) {
            return null;
        }
        $searchCriteriaData = explode('=', $searchCriteria);
        $fieldName = Util::arrayGet($searchCriteriaData, 0);
        $fieldValue = Util::arrayGet($searchCriteriaData, 1);
        if ($fieldName === null || $fieldValue === null) {
            throw new \LogicException('Invalid config entry search criteria');
        }
        foreach ($data as $item) {
            if (Util::arrayGet($item, $fieldName) === $fieldValue) {
                return $item;
            }
        }
        return null;
    }

    public function getEntryFromArray($section, $searchCriteria, $field) {
        $data = $this->getSectionFromArray($section, $searchCriteria);
        return Util::arrayGet($data, $field);
    }
}
