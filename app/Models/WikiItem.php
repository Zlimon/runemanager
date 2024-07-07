<?php

namespace App\Models;

use NumberFormatter;

class WikiItem {
    private $imageUrl;
    private $name;
    private $quantity;
    private $quantityStr;
    private $rarityStr;
    private $rarity;
    private $exchangePrice;
    private $alchemyPrice;

    private $nf;

    public function __construct($imageUrl, $name, $quantity, $quantityStr, $rarityStr, $rarity, $exchangePrice, $alchemyPrice) {
        $this->imageUrl = $imageUrl;
        $this->name = $name;
        $this->quantity = $quantity;
        $this->quantityStr = $quantityStr;
        $this->rarityStr = $rarityStr;
        $this->rarity = $rarity;
        $this->exchangePrice = $exchangePrice;
        $this->alchemyPrice = $alchemyPrice;

        $this->nf = new NumberFormatter('en_US', NumberFormatter::DECIMAL);
    }

    public function getName() {
        return $this->name;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function getQuantityStr() {
        return $this->quantityStr;
    }

    public function getRarity() {
        return $this->rarity;
    }

    public function getRarityStr() {
        return $this->rarityStr;
    }

    public function getExchangePrice() {
        return $this->exchangePrice;
    }

    public function getAlchemyPrice() {
        return $this->alchemyPrice;
    }

    public function getImageUrl() {
        return $this->imageUrl;
    }

    public function getQuantityLabelText() {
        if (strpos($this->quantityStr, '-') !== false || substr($this->quantityStr, -8) === " (noted)") {
            return "x" . $this->quantityStr;
        }
        return $this->quantity > 0 ? "x" . $this->nf->format($this->quantity) : $this->quantityStr;
    }

    public function getQuantityLabelTextShort() {
        if (substr($this->quantityStr, -8) === " (noted)") {
            return "x" . preg_replace('/\(.*\)/', '(n)', $this->quantityStr);
        }
        return $this->getQuantityValueText();
    }

    public function getQuantityValueText() {
        return $this->quantity > 0 ? "x" . $this->rsFormat($this->quantity) : "";
    }

    public function getRarityLabelText($percentMode) {
        $rarityLabelStr = strpos($this->rarityStr, ';') !== false || $this->rarityStr === "Always" || strpos($this->rarityStr, ' × ') !== false ? $this->rarityStr : $this->convertDecimalToFraction($this->rarity);
        if ($percentMode) {
            $rarityLabelStr = $this->toPercentage($this->rarity, $this->rarity <= 0.0001 ? 3 : 2);
        }
        return $rarityLabelStr;
    }

    public function getExchangePriceLabelText() {
        $priceLabelStr = $this->exchangePrice > 0 ? $this->nf->format($this->exchangePrice) . "gp" : "Not sold";
        if ($this->name === "Nothing") {
            $priceLabelStr = "";
        }
        return $priceLabelStr;
    }

    public function getExchangePriceLabelTextShort() {
        $priceLabelStr = $this->exchangePrice > 0 ? $this->rsFormat($this->exchangePrice) : "";
        if ($this->name === "Nothing") {
            $priceLabelStr = "";
        }
        return $priceLabelStr;
    }

    public function getAlchemyPriceLabelText() {
        $priceLabelStr = $this->nf->format($this->alchemyPrice) . "gp";
        if ($this->name === "Nothing" || $this->alchemyPrice < 0) {
            $priceLabelStr = "";
        }
        return $priceLabelStr;
    }

    public function getAlchemyPriceLabelTextShort() {
        $priceLabelStr = $this->alchemyPrice > 0 ? $this->nf->format($this->alchemyPrice) . "gp" : "";
        if ($this->name === "Nothing" || $this->alchemyPrice < 0) {
            $priceLabelStr = "";
        }
        return $priceLabelStr;
    }

    private function rsFormat($number) {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        }
        if ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }
        return (string)$number;
    }

    private function convertDecimalToFraction($decimal) {
        if ($decimal == 0) {
            return '0';
        }
        $tolerance = 1.e-6;
        $h1 = 1; $h2 = 0;
        $k1 = 0; $k2 = 1;
        $b = $decimal;
        do {
            $a = floor($b);
            $aux = $h1; $h1 = $a * $h1 + $h2; $h2 = $aux;
            $aux = $k1; $k1 = $a * $k1 + $k2; $k2 = $aux;
            $b = 1 / ($b - $a);
        } while (abs($decimal - $h1 / $k1) > $decimal * $tolerance);
        return "$h1/$k1";
    }

    private function toPercentage($decimal, $precision = 2) {
        return round($decimal * 100, $precision) . '%';
    }
}
