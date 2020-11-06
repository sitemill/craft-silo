<?php

namespace sitemill\dam\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class DamAssetQuery extends ElementQuery
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
        $this->joinElementTable('dam_assets');

        $this->query->select([
            'dam_assets.uploaderId',
            'dam_assets.assetId',
            'dam_assets.filename',
            'dam_assets.kind',
            'dam_assets.width',
            'dam_assets.height',
            'dam_assets.size',
            'dam_assets.focalPoint',
            'dam_assets.approved',
            'dam_assets.downloads'
        ]);

        if ($this->uploaderId) {
            $this->subQuery->andWhere(Db::parseParam('dam_assets.uploaderId', $this->uploaderId));
        }

        if ($this->assetId) {
            $this->subQuery->andWhere(Db::parseParam('dam_assets.assetId', $this->assetId));
        }

        if ($this->filename) {
            $this->subQuery->andWhere(Db::parseParam('dam_assets.filename', $this->filename));
        }

        if ($this->kind) {
            $this->subQuery->andWhere(Db::parseParam('dam_assets.kind', $this->kind));
        }

        if ($this->width) {
            $this->subQuery->andWhere(Db::parseParam('dam_assets.width', $this->width));
        }

        if ($this->height) {
            $this->subQuery->andWhere(Db::parseParam('dam_assets.height', $this->height));
        }

        if ($this->size) {
            $this->subQuery->andWhere(Db::parseParam('dam_assets.size', $this->size));
        }

        if ($this->focalPoint) {
            $this->subQuery->andWhere(Db::parseParam('dam_assets.focalPoint', $this->focalPoint));
        }

        if ($this->approved) {
            $this->subQuery->andWhere(Db::parseParam('dam_assets.approved', $this->approved));
        }

        if ($this->downloads) {
            $this->subQuery->andWhere(Db::parseParam('dam_assets.downloads', $this->downloads));
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