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
      'margins' => [ 'L' => 20, 'T' => 17, 'R' => 10, 'B' => 12 ],
      'headerMargin' => 5,
      'footerMargin' => 10,
    ],
    'fontsize' => [
      'levelledPara' => [
        'title' =>[12,11,10,9,8],
        'para' => 9, // yang ini nantinya tidak dipakai. Semua dikeluarkan dari levelledPara
        'figure' => [ 
          'title' => 9, 
          'legend' => [
            'header' => 8,
            'list' => 7
          ]
        ],
      ],
      'para' => 9,
    ],
    'fontfamily' => 'tahoma',
    'content' => [
      'padding' => ['levelledPara' => [0,3,5,7,9]],
      'header' => 'pt51_header.php',
      'footer' => 'pt51_footer.php',
      'tablestyle' => [
        'loa' => [
          'cellpadding' => '0mm',
        ],
        'terminologies_notice' => [
          'cellpadding' => '1mm',
        ],
        'alltdcenter' => [
          'cellpadding' => '1mm',
        ],
        'engine_limitation' => [
          'cellpadding' => '1mm',
        ],
        // 'gcs_limitation' => [
        //   'cellpadding' => '1mm',
        // ]
        
      ],
    ],
    'attributes' =>[
      'crewMemberType' => [
        'cm02' => 'PIL',
        'cm03' => 'GCSOP',
        'cm04' => 'GCSOP#1',
        'cm05' => 'GCSOP#2',
        'cm06' => 'GCSOP#3',
        'cm07' => 'Tech',
        'cm08' => 'ALL',
        'cm09' => 'SPOT',
      ]
    ],
  ],
];

$pmEntryType = [
  '' => (fn() => (require __DIR__."/../../attributes.php")['pmEntryType'][''])(),
  'pmt01' => [
    'interpretation' =>  'TITLE PAGE',
    'useheader' => false,
    'usefooter' => false,
    'usetoc' => false,
    'usebookmark' => true,
    'printpageident' => false,
    'page' => [
      'margins' => [ 'L' => 20, 'T' => 10, 'R' => 10, 'B' => 15 ],
    ],
  ],
  'pmt06' => [
    'interpretation' =>  'LEODM',
    'useheader' => true,
    'usefooter' => true,
    'usetoc' => false,
    'usebookmark' => true,
    'printpageident' => true,
  ],
  'pmt08' => [
    'interpretation' =>  'HIGH',
    'useheader' => true,
    'usefooter' => true,
    'usetoc' => false,
    'usebookmark' => true,
    'printpageident' => true,
  ],
  'pmt61' => [
    'interpretation' => 'CONTENT',
    'useheader' => true,
    'usefooter' => true,
    'usetoc' => true,
    'usebookmark' => true,
    'printpageident' => true,
  ],
];

return [
  'pmType' => $pmType,
  'pmEntryType' => $pmEntryType,
];