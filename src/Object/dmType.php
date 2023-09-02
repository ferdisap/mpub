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

/**
 * Array for resolving data module name
 */
$resolveDMName = [
  0 => $default,
  1 => $applicCrossRefTable,
];
