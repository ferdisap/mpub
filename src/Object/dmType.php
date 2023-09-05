<?php

namespace Ptdi\Mpub\Object;

/**
 * $applicCrossRefTable means that data module instances is utilize to resolve ACT data module name
 */
$default = [
  "prefix" => null,
  "xpath" => [
    'dmCode' => "//identAndStatusSection/dmAddress/dmIdent/dmCode",
    'issueInfo' => "//identAndStatusSection/dmAddress/dmIdent/issueInfo",
  ],
];

$applicCrossRefTable = [
  "prefix" => "DMC",
  "xpath" => [
    'dmCode' => "//identAndStatusSection//dmStatus/applicCrossRefTableRef/dmRef/dmRefIdent/dmCode",
    'issueInfo' => "//identAndStatusSection//dmStatus/applicCrossRefTableRef/dmRef/dmRefIdent/issueInfo",
  ]
];

$condCrossRefTable = [
  "prefix" => "DMC",
  "xpath" => [
    'dmCode' => "//content//condCrossRefTableRef/dmRef/dmRefIdent/dmCode",
    'issueInfo' => "//content//condCrossRefTableRef/dmRef/dmRefIdent/issueInfo",
  ]
];
$productCrossRefTable = [
  "prefix" => "DMC",
  "xpath" => [
    'dmCode' => "//content//productCrossRefTableRef/dmRef/dmRefIdent/dmCode",
    'issueInfo' => "//content//productCrossRefTableRef/dmRef/dmRefIdent/issueInfo",
  ]
];

/**
 * Array for resolving data module name
 */
$getDMName = [
  0 => $default,
  1 => $applicCrossRefTable,
  2 => $condCrossRefTable,
  3 => $productCrossRefTable,
];
