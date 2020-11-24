<?php

namespace sitemill\silo\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class SiloAssetQuery extends ElementQuery
{
    public $uploaderId;
    public $assetId;
    public $filename;
    public $kind;
    public $width;
    public $height;
    public $size;
    public $focalPoint;
    public $approved;
    public $downloads;

    public function uploaderId($value) {
        $this->uploaderId = $value;
        return $this;
    }

    public function assetId($value)
    {
        $this->assetId = $value;
        return $this;
    }


    public function filename($value)
    {
        $this->filename = $value;
        return $this;
    }

    public function kind($value)
    {
        $this->kind = $value;
        return $this;
    }

    public function width($value)
    {
        $this->width = $value;
        return $this;
    }

    public function height($value)
    {
        $this->height = $value;
        return $this;
    }

    public function size($value)
    {
        $this->size = $value;
        return $this;
    }

    public function focalPoint($value)
    {
        $this->focalPoint = $value;
        return $this;
    }

    public function approved($value = 1)
    {
        $this->approved = $value;
        return $this;
    }

    public function downloads($value = 1)
    {
        $this->downloads = $value;
        return $this;
    }

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('silo_assets');

        $this->query->select([
            'silo_assets.uploaderId',
            'silo_assets.assetId',
            'silo_assets.filename',
            'silo_assets.kind',
            'silo_assets.width',
            'silo_assets.height',
            'silo_assets.size',
            'silo_assets.focalPoint',
            'silo_assets.approved',
            'silo_assets.downloads'
        ]);

        if ($this->uploaderId) {
            $this->subQuery->andWhere(Db::parseParam('silo_assets.uploaderId', $this->uploaderId));
        }

        if ($this->assetId) {
            $this->subQuery->andWhere(Db::parseParam('silo_assets.assetId', $this->assetId));
        }

        if ($this->filename) {
            $this->subQuery->andWhere(Db::parseParam('silo_assets.filename', $this->filename));
        }

        if ($this->kind) {
            $this->subQuery->andWhere(Db::parseParam('silo_assets.kind', $this->kind));
        }

        if ($this->width) {
            $this->subQuery->andWhere(Db::parseParam('silo_assets.width', $this->width));
        }

        if ($this->height) {
            $this->subQuery->andWhere(Db::parseParam('silo_assets.height', $this->height));
        }

        if ($this->size) {
            $this->subQuery->andWhere(Db::parseParam('silo_assets.size', $this->size));
        }

        if ($this->focalPoint) {
            $this->subQuery->andWhere(Db::parseParam('silo_assets.focalPoint', $this->focalPoint));
        }

        if ($this->approved) {
            $this->subQuery->andWhere(Db::parseParam('silo_assets.approved', $this->approved));
        }

        if ($this->downloads) {
            $this->subQuery->andWhere(Db::parseParam('silo_assets.downloads', $this->downloads));
        }

        return parent::beforePrepare();
    }

    protected function statusCondition(string $status)
    {
        switch ($status) {
            case 'live':
                return [
                    'approved' => true,
                ];
            case 'staged':
                return [
                    'approved' => false
                ];
            default:
                return parent::statusCondition($status);
        }
    }
}