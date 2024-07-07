<?php

namespace App\Helpers;

use App\Models\WikiItem;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\DomCrawler\Crawler;

class WikiScraper {
    private string $baseUrl;
    private $baseWikiUrl;
    private $baseWikiLookupUrl;

    public function __construct()
    {
        $this->baseUrl = "https://oldschool.runescape.wiki";
        $this->baseWikiUrl = $this->baseUrl . "/w/";
        $this->baseWikiLookupUrl = $this->baseWikiUrl . "Special:Lookup";
    }

    public function getDropsByMonster(string $monsterName, int $monsterId = -1): PromiseInterface
    {
        $url = ($monsterId > -1) ? $this->getWikiUrlWithId($monsterName, $monsterId) : $this->getWikiUrl($monsterName);

        $httpClient = new Client();

        return $this->requestAsync($httpClient, $url)->then(function($responseHTML) use ($monsterName) {
            $dropTableSections = [];
            $doc = new Crawler($responseHTML);

            $tableHeaders = $doc->filter('h2 span.mw-headline, h3 span.mw-headline, h4 span.mw-headline');
            $parseDropTableSection = false;
            $currDropTableSection = new DropTableSection();
            $currDropTable = [];
            $tableIndexH3 = 0;
            $tableIndexH4 = 0;
            $incrementH3Index = false;

            foreach ($tableHeaders as $tableHeaderElement) {
                $tableHeaderText = $tableHeaderElement->textContent;
                $monsterNameLC = strtolower($monsterName);

                // Handle edge cases for specific pages
                if ($monsterNameLC === "hespori" && $tableHeaderText === "Main table") continue;
                if ($monsterNameLC === "chaos elemental" && $tableHeaderText === "Major drops") continue;
                if ($monsterNameLC === "cyclops" && $tableHeaderText === "Drops") continue;
                if ($monsterNameLC === "gorak" && $tableHeaderText === "Drops") continue;
                if ($monsterNameLC === "undead druid" && $tableHeaderText === "Seeds") {
                    $incrementH3Index = true;
                    continue;
                }

                $tableHeaderTextLower = strtolower($tableHeaderText);
                $isDropsTableHeader = strpos($tableHeaderTextLower, "drop") !== false || strpos($tableHeaderTextLower, "levels") !== false || $this->isDropsHeaderForEdgeCases($monsterName, $tableHeaderText);
                $isPickpocketLootHeader = strpos($tableHeaderTextLower, "loot") !== false;
                $parseH3Primary = $isPickpocketLootHeader || $this->parseH3PrimaryForEdgeCases($monsterName);

                $parentH2 = $this->closest($tableHeaderElement, 'h2');
                $isParentH2 = $parentH2 !== null;

                $parentH3 = $this->closest($tableHeaderElement, 'h3');
                $isParentH3 = $parentH3 !== null;

                $parentH4 = $this->closest($tableHeaderElement, 'h4');
                $isParentH4 = $parentH4 !== null;

                // Handle edge cases for specific pages
                if ($isParentH3 && $tableHeaderText === "Regular drops") {
                    $incrementH3Index = true;
                    continue;
                }

                if ($isParentH2 || ($parseH3Primary && $isParentH3)) {
                    if (!empty($currDropTable)) {
                        // reset section
                        $currDropTableSection->setTable($currDropTable);
                        $dropTableSections[] = $currDropTableSection;

                        $currDropTable = [];
                        $currDropTableSection = new DropTableSection();
                    }

                    if ($isDropsTableHeader || $isPickpocketLootHeader) {
                        // new section
                        $parseDropTableSection = true;
                        $currDropTableSection->setHeader($tableHeaderText);
                    } else {
                        $parseDropTableSection = false;
                    }
                } elseif ($parseDropTableSection && ($isParentH3 || $isParentH4)) {
                    $element = $isParentH4 ? "h4" : "h3";
                    $tableIndex = $isParentH4 ? $tableIndexH4 : $tableIndexH3;
                    // parse table
                    $tableRows = $this->getTableItems($doc, $tableIndex, $element . " ~ table.item-drops");

                    if (!empty($tableRows) && !isset($currDropTable[$tableHeaderText])) {
                        $currDropTable[$tableHeaderText] = $tableRows;
                        if ($isParentH4) {
                            $tableIndexH4++;
                            if ($incrementH3Index) {
                                $tableIndexH3++;
                            }
                        } else {
                            $tableIndexH3++;
                        }
                    }
                }
            }

            if (!empty($currDropTable)) {
                $currDropTableSection->setTable($currDropTable);
                $dropTableSections[] = $currDropTableSection;
            }

            if (empty($dropTableSections)) {
                $tableHeaders = $doc->filter('h2 span.mw-headline');

                if (!empty($tableHeaders)) {
                    $tableRows = $this->getTableItems($doc, 0, "h2 ~ table.item-drops");
                    if (!empty($tableRows)) {
                        $currDropTable = [];
                        $currDropTable["Drops"] = $tableRows;
                        $dropTableSections[] = new DropTableSection("Drops", $currDropTable);
                    }
                }
            }

            return $dropTableSections;
        });
    }

    private function getTableItems($doc, $tableIndex, $selector) {
        $wikiItems = [];
        $dropTables = $doc->filter($selector);

        if (count($dropTables) > $tableIndex) {
            $dropTableRows = $dropTables->eq($tableIndex)->filter('tbody tr');
            foreach ($dropTableRows as $dropTableRow) {
                $lootRow = [];
                $dropTableCells = (new Crawler($dropTableRow))->filter('td');
                $index = 1;

                foreach ($dropTableCells as $dropTableCell) {
                    $cellContent = $dropTableCell->textContent;
                    $images = (new Crawler($dropTableCell))->filter('img');

                    if (count($images) !== 0) {
                        $imageSource = $images->eq(0)->attr('src');
                        if (!empty($imageSource)) {
                            $lootRow[0] = $this->baseUrl . $imageSource;
                        }
                    }

                    if (!empty($cellContent) && $index < 6) {
                        $cellContent = $this->filterTableContent($cellContent);
                        $lootRow[$index] = $cellContent;
                        $index++;
                    }
                }

                if (!empty($lootRow[0])) {
                    $wikiItem = $this->parseRow($lootRow);
                    $wikiItems[] = $wikiItem;
                }
            }
        }

        return $wikiItems;
    }

    public static function parseRow($row) {
        $imageUrl = "";
        $name = "";

        $rarity = -1;
        $rarityStr = "";

        $quantity = 0;
        $quantityStr = "";
        $exchangePrice = -1;
        $alchemyPrice = -1;

        if (count($row) > 4) {
            $imageUrl = $row[0];
            $name = $row[1];
            if (substr($name, -3) === "(m)") {
                // (m) indicates members only, remove because it's not part of actual item name
                $name = substr($name, 0, -3);
            }

            $quantityStr = str_replace("–", "-", trim($row[2]));
            try {
                $quantityStrs = preg_split('/-/', str_replace(" ", "", $quantityStr));
                $firstQuantityStr = count($quantityStrs) > 0 ? $quantityStrs[0] : null;
                $quantity = intval($firstQuantityStr);
            } catch (\Exception $e) {}

            $rarityStr = $row[3];
            if (substr($rarityStr, 0, 1) === "~") {
                $rarityStr = substr($rarityStr, 1);
            } else if (strpos($rarityStr, "2 × ") === 0 || strpos($rarityStr, "3 × ") === 0) {
                $rarityStr = substr($rarityStr, 4);
            }

            try {
                $rarityStrs = preg_split('/;/', str_replace(" ", "", $rarityStr));
                $firstRarityStr = count($rarityStrs) > 0 ? $rarityStrs[0] : null;

                if ($firstRarityStr !== null) {
                    if ($firstRarityStr === "Always") {
                        $rarity = 1.0;
                    } else {
                        $fraction = preg_split('/\//', $firstRarityStr);
                        if (count($fraction) > 1) {
                            $numer = floatval($fraction[0]);
                            $denom = floatval($fraction[1]);
                            $rarity = $numer / $denom;
                        }
                    }
                }
            } catch (\Exception $ex) {}

            try {
                $exchangePrice = intval($row[4]);
            } catch (\Exception $ex) {}
            try {
                $alchemyPrice = intval($row[5]);
            } catch (\Exception $ex) {}
        }

        return new WikiItem($imageUrl, $name, $quantity, $quantityStr, $rarityStr, $rarity, $exchangePrice, $alchemyPrice);
    }

    public static function filterTableContent($cellContent) {
        return preg_replace('/\[.*\]/', '', $cellContent);
    }

    public function getWikiUrl($itemOrMonsterName) {
        $sanitizedName = self::sanitizeName($itemOrMonsterName);
        return $this->baseWikiUrl . $sanitizedName;
    }

    public function getWikiUrlWithId($monsterName, $id) {
        $sanitizedName = self::sanitizeName($monsterName);
        // Handle edge cases for specific pages
        if ($id == 7851 || $id == 7852) {
            // Redirect Dusk and Dawn to Grotesque Guardians page
            $id = -1;
            $sanitizedName = "Grotesque_Guardians";
        }
        return $this->baseWikiLookupUrl . "?type=npc&id=" . $id . "&name=" . $sanitizedName;
    }

    public function getWikiUrlForDrops($monsterName, $anchorText, $monsterId) {
        if ($monsterId > -1) {
            return $this->getWikiUrlWithId($monsterName, $monsterId);
        }
        $sanitizedMonsterName = self::sanitizeName($monsterName);
        $anchorStr = "Drops";
        if ($anchorText !== null) {
            $anchorStr = preg_replace('/\s+/', "_", $anchorText);
        }
        return $this->baseWikiUrl . $sanitizedMonsterName . "#" . $anchorStr;
    }

    public static function sanitizeName($name) {
        // Handle edge cases for specific pages
        if (strtolower($name) === "tzhaar-mej") {
            $name = "tzhaar-mej (monster)";
        }
        if (strtolower($name) === "dusk" || strtolower($name) === "dawn") {
            $name = "grotesque guardians";
        }
        $name = trim(strtolower($name));
        $name = preg_replace('/\s+/', "_", $name);
        return ucfirst($name);
    }

    public static function isDropsHeaderForEdgeCases($monsterName, $tableHeaderText) {
        $monsterNameLC = strtolower($monsterName);
        $tableHeaderTextLower = strtolower($tableHeaderText);
        return ($monsterNameLC === "cyclops" && (
                strpos($tableHeaderTextLower, "warriors' guild") !== false ||
                $tableHeaderText === "Ardougne Zoo")) ||
                ($monsterNameLC === "vampyre juvinate" && $tableHeaderTextLower === "returning a juvinate to human");
    }

    public static function parseH3PrimaryForEdgeCases($monsterName) {
        return strtolower($monsterName) === "cyclops";
    }

    private function requestAsync(Client $httpClient, $url) {
        $promise = $httpClient->requestAsync('GET', $url, [
            'headers' => ['User-Agent' => 'Runemanager Wiki Scraper/2.0 (+zlimon@runemanager.com)']
        ]);

        return $promise->then(
            function ($response) {
                return $response->getBody()->getContents();
            },
            function (RequestException $e) {
                return $e->getMessage();
            }
        );
    }

    private function closest($element, $selector) {
        $crawler = new Crawler($element);
        while ($element !== null) {
            $element = $element->parentNode;
            if ($element !== null && $crawler->filter($selector)->count() > 0) {
                return $element;
            }
        }
        return null;
    }
}
