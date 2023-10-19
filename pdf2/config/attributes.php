<?php



$pmType = [
  'pt51' => [
    'interpretation' => 'POH-AFM',
    'value' => 'pt51',
    'useheader' => false,
    'usefooter' => false,
    'usetoc' => false,
    'usebookmark' => false,
    'page' => [
      'orientation' => 'P',
      'unit' => 'mm',
      'format' => 'A5',
      'margins' => [ 'L' => 20, 'T' => 25, 'R' => 10, 'B' => 20 ],
      // 'margins' => [ 'L' => 20, 'T' => 25, 'R' => 10, 'B' => 25 ],
      'headerMargin' => 5,
      'footerMargin' => 15,
    ],
    'fontsize' => [
      'levelledPara' => [
        'title' =>[12,11,10,9,8],
        'para' => 9,
        'figure' => [ 
          'title' => 9, 
          'legend' => [
            'header' => 8,
            'list' => 7
          ]
        ],
      ],
    ],
    'content' => [
      'padding' => [
        'levelledPara' => [0,3,5,7,9]
      ],
      'header' => '/template/pt51_header.php',
      'footer' => '/template/pt51_footer.php'
    ]
  ],
];

$pmEntryType = [
  'pmt01' => [
    'interpretation' =>  'TITLE PAGE',
    'useheader' => false,
    'usefooter' => false,
    'usetoc' => false,
    'usebookmark' => true,
    'page' => [
      'margins' => [ 'L' => 20, 'T' => 15, 'R' => 10, 'B' => 15 ],
    ],
  ],
  'pmt06' => [
    'interpretation' =>  'LEODM',
    'useheader' => true,
    'usefooter' => true,
    'usetoc' => false,
    'usebookmark' => true,
  ],
  'pmt08' => [
    'interpretation' =>  'HIGH',
    'useheader' => true,
    'usefooter' => true,
    'usetoc' => false,
    'usebookmark' => true,
  ],
  'pmt61' => [
    'interpretation' => 'CONTENT',
    'useheader' => true,
    'usefooter' => true,
    'usetoc' => true,
    'usebookmark' => true,
  ],
];

return [
  'pmType' => $pmType,
  'pmEntryType' => $pmEntryType,
];