<?php

namespace Ptdi\Mpub\Main;

class Helper
{
  /**
   * DEPRECIATED
   * Fungsi ini akan menscan semua (nested) CSDB Object referenced
   * @return Array
   */
  // public static function scanObjectRef(\DOMDocument $doc)
  // {
  //   $doc_name = CSDB::resolve_DocIdent($doc);
  //   $an = self::analyzeURI($doc->baseURI);

  //   $scan = function ($base_doc) use ($an) {
  //     $docXpath = new \DOMXPath($base_doc);
  //     $xpath = '//dmlRef | //dmRef | //pmRef | //infoEntityRef';
  //     $res = $docXpath->evaluate($xpath);
  //     // dd($res->length);
  //     $found = [];
  //     $unfound = [];
  //     foreach ($res as $k => $r) {
  //       $tagName = str_replace('Ref', 'Ident', $r->tagName);
  //       $name = call_user_func_array(CSDB::class . "::resolve_{$tagName}", [$r]);
  //       $uri = $an['path'] . DIRECTORY_SEPARATOR . $name;
  //       if (file_exists($uri)) {
  //         $found[] = $name;
  //       } else {
  //         $unfound[] = $name;
  //       }
  //     }
  //     return [$found, $unfound];
  //   };
  //   $scanResult = $scan($doc);
  //   $found_name = $scanResult[0];
  //   $unfound_name = $scanResult[1];

  //   // #1. scan taip hasil temuan document ($doc)
  //   $loop = 0;
  //   while (isset($found_name[$loop]) and ($found_doc = (CSDB::importDocument($an['path'], $found_name[$loop])))) {
  //     $scanResult = $scan($found_doc);
  //     $found_name = array_merge($scanResult[0], $found_name);
  //     $unfound_name = array_merge($scanResult[1], $unfound_name);

  //     $found_name = array_unique($found_name);
  //     $unfound_name = array_unique($unfound_name);
  //     $loop++;
  //   }

  //   // #2. tambahkan dokumen base di index ke 0;
  //   array_unshift($found_name, $doc_name);

  //   return [
  //     'found' => $found_name,
  //     'unfound' => $unfound_name,
  //   ];
  // }

  private static function getClauseForQuery()
  {
  }

  /**
   * masih terbatas pada column integer/string, belum json
   * 
   * jika "?sc=DMC" => maka querynya WHERE each.column like %DMC% , joined by 'OR';
   * jika "?sc=filename::DMC%20path::csdb" => maka querynya WHERE filename LIKE '%DMC%' AND path LIKE '%csdb%';
   * jika "?sc=filename::DMC,PMC" => maka querynya WHERE filename LIKE '%DMC%' OR filename LIKE '%PMC%';
   * jika "?sc=filename::DMC%20filename::022" => maka querynya WHERE filename LIKE '%DMC%' AND filename LIKE '%022%';
   * @param Array, index0 = query string, index1 = exploded keywords
   * @return Array
   * 
   * OUTPUT: " ( (A AND B) OR (A AND C) )" if keyword contains column [A[],[B,C]
   */
  public static function generateWhereRawQueryString($keyword, string $table, array $strictString = ['col' => "%#&value;%"])
  {
    // $isFitted = false;
    // contoh1
    // $keywords = [
    //   'path' => ['A','B'],
    //   'filename' => ['C','D', 'E'],
    //   'editable' => ['F','G'],
    // ];
    // contoh2
    // $keywords = [
    //   'path' => ['A','B'],
    //   'filename' => ['C'],
    // ];
    // contoh3
    // $keywords = [
    //   'path' => ['A'],
    //   'filename' => ['B','C','D'],
    //   'editable' => ['E'],
    // ];
    $keywords = is_array($keyword) ? $keyword : self::explodeSearchKeyAndValue($keyword);
    if (empty($keywords)) return [];

    // dd($keywords);

    // jika $keyword tidak ada column namenya, maka akan mengambil seluruh column name database
    // contoh $request->sc = "Senchou";. Kita tidak tahu 'Senchou' ini dicari di column mana, jadi cari di semua column di database
    // $table = $table ? $table : ($this->model instanceof Builder ? $this->model->getModel()->getTable() : $this->model->getTable());
    // $fitToColumn = function($keywordsExploded)use($table){
    //   $column = DB::getSchemaBuilder()->getColumnListing($table);
    //   for ($i=0; (int)$i < count($column); $i++) { 
    //     $k = $column[$i];
    //     $column[$k] = $keywordsExploded;
    //     unset($column[$i]);
    //   }
    //   return $column;
    // };

    // if(isset($this->model) && (get_class($this->model) === Csdb::class)){
    //   if(array_is_list($keywords)){
    //     $keywords = $fitToColumn($keywords);
    //     $isFitted = true;
    //   }
    //   // $keywords['path'] = array_map(fn($v) => $v = substr($v,-1,1) === '/' ? $v : $v . "/", $keywords['path']);
    //   // $keywords['initiator_id'] = $keywords['initiator_id'] ?? [Auth::user()->id]; // kayaknya ini ga perlu karena suatu saat ada orang yang import csdb object pakai DDN
    //   $keywords['available_storage'] = [Auth::user()->storage];
    // } else {
    //   if(array_is_list($keywords)){
    //     $keywords = $fitToColumn($keywords);
    //     $isFitted = true;
    //   }
    // }
    // dump($keywords);

    // create space
    $keys = array_keys($keywords);
    // deprecated jika $space value sudah di deklarasikan di fungsi
    // $createSpace = function ($k, $space = '', $cb) use ($keywords, $keys) {
    $createSpace = function ($k, $space, $cb) use ($keywords, $keys) {
      // create space
      $queryArr = $keywords[$keys[$k]];
      $l = count($queryArr);
      $isNextCol = isset($keys[$k + 1]);
      $squareOpen = 0;
      $curvOpen = 0;
      if ($l - 1 > 0 and $isNextCol) {
        $space .= "{";
        $curvOpen++;
      } elseif ($l - 1 > 0) {
        $space .= "[";
        $squareOpen++;
      }
      // untuk perbaikan contoh3 dan contoh3
      elseif ($l === 1 and !$isNextCol) {
        $space .= "[";
        $squareOpen++;
      } else {
        $space .= "{";
        $curvOpen++;
      };
      for ($i = 0; $i < $l; $i++) {
        $isNextIndex = $i + 1 < $l;
        $space .= '"COL' . $k . '_' . $i . '"';
        if ($isNextCol) {
          $space .= ":";
          $space .= $cb($k + 1, '', $cb);
        }
        if ($isNextIndex) $space .= ",";
      }
      while ($curvOpen > 0) {
        $space .= "}";
        $curvOpen--;
      }
      while ($squareOpen > 0) {
        $space .= "]";
        $squareOpen--;
      }
      return $space;
    };
    $space = $createSpace(0, '', $createSpace);

    // fill the space
    $vCode = " ? ";
    $dictionary = array();
    $dictionaryBindValue = array();
    foreach ($keywords as $col => $queryArr) {
      $colnum = array_search($col, $keys);
      if ($col === 'typeonly') $col = 'filename';
      foreach ($queryArr as $i => $v) {
        $indexString = "COL{$colnum}_{$i}";
        $id = rand(0, 9999); // mencegah kalau kalau ada value yang sama antar column
        // $escapedV = str_replace("_", "\_",$v); // sudah dicoba di SQLITE tapi error di MySQL
        $escapedV = str_replace("_", "|_", $v); // sudah dicoba di MySQL (belum dicoba di SQLITE) dan sesuai ref book MySQL 8.4 page 2170/6000
        $strictStr = str_replace('#&value;', $escapedV, $strictString[$col] ?? "%#&value;%"); // variable $strictString jangan di re asign
        $col = preg_replace("/___[0-9]+$/", "", $col); // menghilangkan suffix "___XXX" yang ditambahkan di fungsi ...Main\Helper::class@explodeSearchKeyAndValue
        // $dictionary["<<".$v.$id.">>"] = " {$col} LIKE '{$strictStr}' ESCAPE '\'"; // // sudah dicoba di SQLITE tapi error di MySQL
        // $dictionary["<<".$v.$id.">>"] = " {$col} LIKE '{$strictStr}' ESCAPE '|'"; // sudah dicoba di MySQL (belum dicoba di SQLITE) dan sesuai ref book MySQL 8.4 page 2170/60004 https://downloads.mysql.com/docs/refman-8.4-en.a4.pdf
        // ditambah $table.$col agar query tidak bingung karena ada table name sebelum column
        // $dictionary["<<".$v.$id.">>"] = " {$table}.{$col} LIKE '{$strictStr}' ESCAPE '|'"; // sudah dicoba di MySQL (belum dicoba di SQLITE) dan sesuai ref book MySQL 8.4 page 2170/60004 https://downloads.mysql.com/docs/refman-8.4-en.a4.pdf
        $dictionary["<<" . $v . $id . ">>"] = " {$table}.{$col} LIKE {$vCode} ESCAPE '|'"; // sudah dicoba di MySQL (belum dicoba di SQLITE) dan sesuai ref book MySQL 8.4 page 2170/60004 https://downloads.mysql.com/docs/refman-8.4-en.a4.pdf
        $space = str_replace($indexString, "<<" . $v . $id . ">>", $space);
        $dictionaryBindValue["<<" . $v . $id . ">>"] = $strictStr;
      }
    }

    // change the filled space to the final string query
    $arr = json_decode($space, true);
    $str = '';
    $merge = function ($prevVal, $arr, $cb) {
      $str = '';
      // $joinAND = !$isFitted ? ' AND ' : ' OR '; // kalau di fittedkan artinya satu keyword untuk mencari semua column. Artinya query SQL akan join pakai OR
      $joinAND = ' AND ';
      if (array_is_list($arr)) {
        foreach ($arr as $i => $v) {
          if ($prevVal) $arr[$i] = "$prevVal" . $joinAND . "$v";
          else $arr[$i] = "$v";
          $arr[$i] = "(" . $arr[$i] . ")"; // tambahan agar setiap setelah AND akan di kurung
        }
        $str = join(" OR ", $arr);
      } else { // jika bukan aray assoc maka berarti ini adalah kolom terakhir
        foreach ($arr as $i => $v) {
          if ($prevVal) $arr[$i] = $cb($prevVal . $joinAND . $i, $v, $cb);
          else $arr[$i] = $cb($prevVal . $i, $v, $cb);
        }
        $str = join(" OR ", $arr);
      }
      return $str;
    };
    $str = "(" . $merge($str, $arr, $merge) . ")"; // dikurung agar tidak tergabung dengan variable $options

    // replace string by the dictionary value and make bindValue and types
    preg_match_all("/<<[^>]+>>/", $str, $bindValue, PREG_PATTERN_ORDER, 0);
    $bindValue = join(";;;;", $bindValue[0]); // match regex adalah $result = [ ['','',''] ], jadi [0] adalah mengambil isi match nya;
    $types = $bindValue;
    foreach ($dictionary as $k => $v) {
      $str = str_replace($k, $v, $str);
      $bindValue = str_replace($k, $dictionaryBindValue[$k], $bindValue);
      $types = str_replace($k, (is_integer($dictionaryBindValue[$k]) ?  'i' : (is_double($dictionaryBindValue[$k]) ? 'd' : 's')), $types);
    }
    $bindValue = explode(";;;;", $bindValue);
    $types = str_replace(";;;;", ',', $types);

    return [(string)$str, (array)$bindValue, (string)$vCode, (string) $types, $keywords];
  }


  /**
   * tidak comply binding params
   * XML string tidak boleh ada dtd nya (tidak boleh ada DOCTYPE nya);
   * walaupun hasil xpath ada 2 value, semua string akan di join pakai space (default function ExtractValue)
   * basic select query is table named 'csdb';
   * 
   * example: 
   * $xml = new Dmc();
   * $xml->selectRawQueryExtractValueXML(['@infoEntityIdent' => 'ICN']);
   * $xml->get()->toArray();
   * 
   * @param {string} table name of target object 
   * @param {array} associative array where key is the element/attribute/xpath and value is value of the node. It is oke if value is null
   * @return Illuminate\Database\Eloquent\Builder
   * 
   * TES XML XPATH di SQL
   * source: https://stackoverflow.com/questions/11754781/how-to-declare-a-variable-in-mysql
   * source: https://dev.mysql.com/doc/refman/8.4/en/xml-functions.html#function_extractvalue
   * $conn = new mysqli('localhost', 'root', 'root', 'techpub');
   * gagal: $query = 'SET @xml = (SELECT xml FROM dmc); SELECT ExtractValue(@xml, "//identAndStatusSection");';
   * gagal: $query = "SET @xml = '<a><b>X</b></a>'; SELECT ExtractValue(@xml, '//b');";
   * gagal: $query = "SELECT @xml := '<a><b>X</b><b>Y</b></a>'; SELECT @i :=1, @j := 2;SELECT @i, ExtractValue(@xml, '//b[$@i]');";
   * berhasil: $query = "SELECT ExtractValue(@xml := '<a><b>X</b><b>Y</b></a>', '//b[$2]');";
   * $result = $conn->query($query); if ($result->num_rows > 0) { while($row = $result->fetch_assoc()) {dump($row);}} $conn->close();
   * 
   * OUTPUT: (`csdb`.`filename` IN (SELECT `filename` FROM `dmc` AS `object` WHERE `object`.`storage` = 'diNnH' AND ExtractValue(@xml := (SELECT `dmc`.`xml` FROM `dmc` WHERE `dmc`.`filename` = `object`.`filename`), '//@modelIdentCode[contains(.,\"CN235\")]') <> ''))
   * 
   * contoh pakai di laravel
   * $query = Helper::generateWhereRawRawQueryExtractValueXML(['@modelIdentCode' => 'CN235'], 'dmc', $request->user()->storage);
   * $query = Helper::generateWhereRawRawQueryExtractValueXML(['simplePara' => 'MALE'], 'dmc', $request->user()->storage);
   * $query = Helper::generateWhereRawRawQueryExtractValueXML(['simplePara' => 'N219'], 'dmc', $request->user()->storage);
   * $query = Helper::generateWhereRawRawQueryExtractValueXML(['*' => 'High Pressure Shut-off Valve'], 'dmc', $request->user()->storage);
   * $query = Helper::generateWhereRawRawQueryExtractValueXML(['*' => 'PT Dirgantara Indonesia'], 'dmc', $request->user()->storage);
   * $xml = new Csdb();
   * $xml = $xml->whereRaw($query[0], $query[1]);
   * $xml->get();
   */
  public static function generateWhereRawRawQueryExtractValueXML(array $searchKey, string $table, string $storage)
  {
    // contoh output tidak di binded
    // $query = "SELECT `filename` FROM `{$table}` AS `object` WHERE `object`.`storage` = '{$storage}' AND ExtractValue(@xml := (SELECT `{$table}`.`xml` FROM `$table` WHERE `{$table}`.`filename` = `object`.`filename`), '{$xpath}') <> ''";
    $vCode = " ? ";
    $xpath = '';
    $bindValue = [];
    $types = '';
    // $query = 'SELECT * from `csdb` WHERE csdb.filename IN ';
    $query = '(`csdb`.`filename` IN ';
    // $query = '';
    foreach ($searchKey as $key => $value) {
      $xpath .= "|";
      $xpath .= "//" . $key;
      // if($value) $xpath .= "["."contains(.,'".$value."')"."]";
      if ($value) $xpath .= "[" . "contains(.," . '"' . $value . '"' . ")" . "]";
    }
    $xpath = substr($xpath, 1); // untuk menghilangkan symbol pipe "|" di awal
    $query .= "(SELECT `filename` FROM `{$table}` AS `object` WHERE `object`.`storage` = {$vCode} AND ExtractValue(@xml := (SELECT `{$table}`.`xml` FROM `$table` WHERE `{$table}`.`filename` = `object`.`filename`), {$vCode}) <> '')";
    $query .= ")";
    $bindValue[] = $storage;
    $types .= 's';
    $bindValue[] = $xpath;
    $types .= 's';
    return [(string)$query, (array)$bindValue, (string)$vCode, (string)$types];
  }

  /**
   * ini untuk DEVELOPMENT saja karena resiko SQL injection
   * $types sesuai dengan php @mysqli_stmt_bind_param
   */
  public static function replaceSQLQueryWithBindedParam(string $query, array $bindedValue, string $vCode, string $types = '')
  {
    $vCode = str_replace(" ", '\s', $vCode);
    $vCode = str_replace("?", '\?', $vCode);
    foreach ($bindedValue as $k => $value) {
      // $value = "A'pp\\Models\\Csdb"; misal ada single quote "'" atau single forward slahs '\' maka akan SQL query akan direplace valu terkait menjadi => A\'pp\\Models\\Csdb (ada slash/escape nya) karena di fungsi bawah pakai php@addslash()
      $value = preg_replace("/([^\\\\]+)(\\\\{1,})/", '${1}\\\\\\', $value); // membuang multiple "\\\\" menjadi menjadi double "\\"
      if ($types && (($ty = substr($types, $k, 1)) === 'i' || ($ty === 'd'))) {
        $query = preg_replace("/" . $vCode . "/", $value, $query, 1);
      } else if ($types && $ty === 's') {
        $query = preg_replace("/" . $vCode . "/", "'" . addslashes($value) . "'", $query, 1);
      } else {
        switch (gettype($value)) {
          case 'string':
            $value = "'" . addslashes($value) . "'";
            break;
        }
        $query = preg_replace("/" . $vCode . "/", $value, $query, 1);
      }
    }
    return $query;
  }


  /**
   * uri harus berupa filename 
   * @return Array
   */
  public static function analyzeURI(string $uri): array
  {
    $regex = '/(^[a-z]+:[\/\\\\\\\\]{1,3})|(.+(?=[\/\\\\]))|([^\/^\\\\]+$)/';
    preg_match_all($regex, $uri, $matches, PREG_UNMATCHED_AS_NULL, 0); // 3 elements

    $protocol = array_values(array_filter($matches[1], fn ($v) => $v));
    $path = array_values(array_filter($matches[2], fn ($v) => $v));
    $filename = array_values(array_filter($matches[3], fn ($v) => $v));

    $ret = [
      'uri' => $uri,
      'protocol' => $protocol[0] ?? '',
      // 'path' => $path[0] ?? '',
      'path' => isset($path[0]) ? trim($path[0], "\/\\") : '',
      'filename' => $filename[0] ?? '',
    ];
    $ret = array_map(fn ($v) => $v = str_replace('%20', ' ', $v), $ret);
    return $ret;
  }



  /**
   * DEPRECIATED, diganti oleh explodeSearchKeyAndValue
   * separator adalah '::'.
   * @param mixed $key is string or null
   * @return Array
   */
  public static function explodeSearchKey(mixed $key): array
  {
    $m = [];
    preg_match_all("/[\w]+::[\s\S]*?(?=\s\w+::|$)/", $key, $matches, PREG_SET_ORDER, 0);
    $pull = function (&$arr, $fn) use (&$m) {
      foreach ($arr as $k => $v) {
        if (is_array($v)) {
          $fn($v, $fn);
        } else {
          $xplode = explode("::", $v);
          $m[strtolower($xplode[0])] = $xplode[1];
        }
        unset($arr[$k]);
      }
    };
    $pull($matches, $pull); // $matches akan empty, $m akan berisi
    return !empty($m) ? $m : [$key];
  }

  /**
   * $casting mustbe assoc array [$old => $new]; Kalau pada dasarnya sudah ada $new, maka tidak akan di merger atau di replace. Setiap yang sudah di casting akan di hapus dari dasar (return array nya)
   * $casting key adalah column dataabse
   * 
   * * CONTOH:
   * $request ?sc=DMC-...
   * $keywords = Helper::explodeSearchKeyAndValue($request->get('sc'), 'filename');
   * default key adalah filename
   * 
   * CONTOH:
   * $request ?sc=path::csdb/amm
   * $keywords = Helper::explodeSearchKeyAndValue($request->get('sc'), 'filename');
   * default key adalah filename, namun diset menajdi path   * 
   * 
   * @return Array
   */
  public static function explodeSearchKeyAndValue(mixed $key, string $defaultKey = '', array $casting = []): array
  {
    preg_match_all("/([\w]+::)?([\S]+)/", $key, $m,  PREG_SET_ORDER, 0); // [[0]=matches, [1]=column, [2]=value]
    $length = count($m);
    for ($i = 0; $i < $length; $i++) {
      $key = str_replace("::", "", ($m[$i][1] ? $m[$i][1] : $defaultKey));
      $key = !isset($m[$key]) ? $key : $key . "___" . rand(0, 99999); // untuk menghindari column yang sama, see artisan route Controller@generateWhereRawQueryString
      $value = self::exploreSearchValue($m[$i][2]);
      $m[$key] = $value;
      unset($m[$i]);
    }


    // casting
    $ret = (!empty($m) ? $m : ($key ? [$key] : []));
    if (!empty($casting)) {
      foreach ($casting as $old => $new) {
        if (!(isset($ret[$new]))) $ret[$new] = $ret[$old];
      }
      unset($ret[$old]);
    } else {
      // yang tidak ada key nya akan digabung ke key sebelumnya
      $keys = array_keys($ret);
      foreach ($keys as $i => $key) {
        if (!$key && $ret[$keys[$i - 1]]) {
          $ret[$keys[$i - 1]] = array_merge($ret[$keys[$i - 1]], $ret[$key]);
          unset($ret[$key]);
        }
      }
    }
    return $ret;
  }

  public static function exploreSearchValue(string $value): array
  {
    $m = explode(",", $value);
    $m = array_filter($m, fn ($v) => $v !== '');
    $m = array_values($m);
    return $m;
  }

  /**
   * DEPRECIATED. Dipindah ke \CSDBObject class
   */
  public static function arrayify_applic($applic, $keepOneByOne = false, $useDisplayName = true)
  {
    $doc = $applic->ownerDocument;
    $path = self::analyzeURI($doc->baseURI)['path'];
    $domxpath = new \DOMXPath($doc);
    $dmRefIdent = $domxpath->evaluate("//identAndStatusSection/descendant::applicCrossRefTableRef/descendant::dmRefIdent")[0];
    $ACTdoc = CSDB::importDocument($path, CSDB::resolve_dmIdent($dmRefIdent), null, 'dmodule');

    if ($ACTdoc) {
      $domxpath = new \DOMXPath($ACTdoc);
      $dmRefIdent = $domxpath->evaluate("//identAndStatusSection/descendant::applicCrossRefTableRef/descendant::dmRefIdent")[0];
      $CCTdoc = CSDB::importDocument($path, CSDB::resolve_dmIdent($dmRefIdent), null, 'dmodule');
    }

    $id = $applic->getAttribute('id');
    $childApplic = self::children($applic);
    $result = [];
    // $applicability = [];
    foreach ($childApplic as $child) {
      // $result[$child->tagName] = $resolver($child, $resolver);
      $result[$child->tagName] = self::resolver_childApplic($child, $ACTdoc, $CCTdoc, null, $keepOneByOne, $useDisplayName);
    }
    return ($id) ? ($applicability[$id] = $result) : $applicability[] = $result;
  }

  /**
   * DEPRECIATED. Dipindah ke \CSDBObject class
   * @return array containing ['text' => String, ...]
   */
  private static function resolver_childApplic(\DOMElement $child, $ACTdoc, $CCTdoc, $PCTdoc, $keepOneByOne, $useDisplayName)
  {
    switch ($child->tagName) {
      case 'displayText':
        $displayText = '';
        foreach ($child->childNodes as $simplePara) {
          $displayText .= ', ' . $simplePara->textContent;
        }
        $displayText = rtrim($displayText, ", ");
        $displayText = ltrim($displayText, ", ");
        return ["text" => $displayText];
        break;
      case 'assert':
        return self::test_assert($child, $ACTdoc, $CCTdoc, $PCTdoc, $keepOneByOne, $useDisplayName);
        break;
      case 'evaluate':
        return self::test_evaluate($child, $ACTdoc, $CCTdoc, $PCTdoc, $keepOneByOne, $useDisplayName);
        break;
      default:
        return '';
    }
  }

  /**
   * DEPRECIATED. Dipindah ke \CSDBObject class
   * kalau test fail, key 'text' akan di isi oleh <assert> text content dan status menjadi 'success'. Sehingga saat di <evaluate> akan true;
   * @param bool $keepOneByOne 
   * @return array ['text' => String, '%STATUS' => String ('success' or 'fail'), '%APPLICPROPERTYTYPE' => String, '%APPLICPROPERTYIDENT' => String, %APPLICPROPERTYVALUES' => String];
   */
  public static function test_assert(\DOMElement $assert, $ACTdoc = null, $CCTdoc = null, $PCTdoc = null, bool $keepOneByOne = false, bool $useDisplayName = true)
  {
    foreach ($assert->attributes as $att) {
      if (!in_array($att->nodeName, ['applicPropertyIdent', 'applicPropertyType', 'applicPropertyValues'])) {
        return ['text' => $assert->textContent];
      }
    }

    $applicPropertyIdent = $assert->getAttribute('applicPropertyIdent');
    $applicPropertyType = $assert->getAttribute('applicPropertyType');
    $applicPropertyValues = $assert->getAttribute('applicPropertyValues');


    // #1 getApplicPropertyValuesFromCrossRefTable
    // validation CCTdoc
    $crossRefTable = ($applicPropertyType === 'prodattr') ? $ACTdoc : $CCTdoc;
    if (!$crossRefTable) {
      CSDB::setError('getApplicability', join(", ", CSDB::get_errors(true, 'file_exists')));
      return ['text' => ''];
    }

    $crossRefTableDomXpath = new \DOMXPath($crossRefTable);
    if (str_contains(($schema = $crossRefTable->firstElementChild->getAttribute('xsi:noNamespaceSchemaLocation')), 'appliccrossreftable.xsd')) {
      $query_enum = "//enumeration[parent::*/@id = '{$applicPropertyIdent}']/@applicPropertyValues";
      $valueDataType = $crossRefTableDomXpath->evaluate("//productAttribute[@id = '{$applicPropertyIdent}']");
      $valueDataType = (count($valueDataType) > 0) ? ($valueDataType[0]->getAttribute('valueDataType') ?? null) : null;

      $displayName = $crossRefTableDomXpath->evaluate("//productAttribute[@id = '{$applicPropertyIdent}']/displayName");
      $displayName = isset($displayName[0]) ? $displayName[0]->textContent : '';
    } elseif (str_contains(($schema = $crossRefTable->firstElementChild->getAttribute('xsi:noNamespaceSchemaLocation')), 'condcrossreftable.xsd')) {
      $query_condTypeRefId = "//cond[@id = '{$applicPropertyIdent}']/@condTypeRefId";
      $condTypeRefId = $crossRefTableDomXpath->evaluate($query_condTypeRefId);
      $condTypeRefId = $condTypeRefId[0]->value;
      $query_enum = "//enumeration[parent::*/@id = '{$condTypeRefId}']/@applicPropertyValues";

      $valueDataType = $crossRefTableDomXpath->evaluate("//condType[@id = '{$condTypeRefId}']");
      $valueDataType = (count($valueDataType) > 0) ? ($valueDataType[0]->getAttribute('valueDataType') ?? null) : null;

      $displayName = $crossRefTableDomXpath->evaluate("//condType[@id = '{$condTypeRefId}']/displayName");
      $displayName = isset($displayName[0]) ? $displayName[0]->textContent : '';
    } else {
      return ['text' => ''];
    }
    $enums = $crossRefTableDomXpath->evaluate($query_enum);
    $applicPropertyValuesFromCrossRefTable = '';
    $pattern = $crossRefTableDomXpath->evaluate("//@valuePattern[parent::*/@id = '$applicPropertyIdent']");
    $pattern = (count($pattern) > 0) ? $pattern[0]->nodeValue : null;
    if (count($enums) == 0) {
      // isexistValuePattern()
      if ($pattern) {
        $propertyValue = trim($pattern);
        $propertyValue = substr_replace($propertyValue, "", 0, 1);
        $propertyValue = substr_replace($propertyValue, "", strlen($propertyValue) - 1, 1);
        $applicPropertyValuesFromCrossRefTable = $propertyValue;
      }
    } else {
      $applicPropertyValuesFromCrossRefTable = $enums[0]->value;
    }

    // #2 generateValue for Nominal and Produced/actual value
    $generateValue = function (string $applicPropertyValues) use ($valueDataType, $pattern) {
      $values_generated = array();
      // breakApplicPropertyValues()
      // $applicPropertyValues = "N071|N001N005`N010|N015throughN020|N020|N030~N035|N001~N005~N010";
      // $regex[0] untuk match ->N030~N035<- ->N001~N005~N010<-
      // $regex[1] untuk match ->N071<- ->N015throughN020<- ->N020<-
      // semua value yang akan di cek terhadap @valuePattern (jika @valueDataType is string) ada dalam match-group ke 1(index ke 1) atau 2 atau 3
      // jika range (tilde) maka $start = group 1; $end = group 2
      // jika singe value maka group 3
      $regex = ["([A-Za-z0-9\-\/]+)~([A-Za-z0-9\-\/]+)(?:[~`!@#$%^&*()\-_+={}\[\]\\;:'" . '",<.>\/? A-Za-z0-9]+)*', "|", "(?<![`~!@#$%^&*()-_=+{}\[\]\\;;'" . '",<.>\/? ])([A-Za-z0-9\-\/]+)(?![`~!@#$%^&*()-_=+{}\[\]\\;;' . "',<.>\/? ])"]; // https://regex101.com/r/vKhlJB/3 account ferdisaptoko@gmail.com
      $regex = "/" . implode($regex) . "/";
      preg_match_all($regex, $applicPropertyValues, $matches, PREG_SET_ORDER, 0); // matches1 = "N003~N005", matches2 = "N010~N015"
      foreach ($matches as $values) {
        // get start value for iterating
        $start = null;
        $end = null;
        $singleValue = null;
        if ($valueDataType != 'string') {
          $start = $values[1];
          $end = $values[2];
          $singleValue = (isset($values[3]) and $values[3]) ? $values[3] : null;
        } else {
          if (!empty($pattern)) { // jika mau di iterate
            preg_match_all($pattern, $values[1], $matches, PREG_SET_ORDER);
            $start = isset($matches[0][0]) ? $matches[0][1] : null;
            preg_match_all($pattern, $values[2], $matches, PREG_SET_ORDER);
            $end = isset($matches[0][0]) ? $matches[0][1] : null;
            if ((isset($values[3]) and $values[3])) {
              preg_match_all($pattern, $values[2], $matches, PREG_SET_ORDER);
              $singleValue = isset($matches[0][0]) ? $matches[0][1] : null;
            }
          }
        }
        if ($start and $end) {
          $range = range($start, $end);
          foreach ($range as $v) ($values_generated[] = $v);
        }
        if ($singleValue) {
          $values_generated[] = $singleValue;
        }
      }
      return $values_generated;
    };

    $nominalValues = $generateValue($applicPropertyValuesFromCrossRefTable);
    $producedValues = $generateValue($applicPropertyValues);
    $testedValues = array();
    $successValues = array();
    $failValues = array();
    if (!empty($nominalValues) and !empty($producedValues)) {
      $status = 'success';
      foreach ($producedValues as $value) {
        // walaupun aday ang ga match antara produced dan nominal values, tidak membuat semuanya false
        // $testedValues[] = $value;
        // if (!in_array($value, $nominalValues)) $status = 'fail'; // jika ada yang tidak sama, maka dikasi status fail, tapi tetap masuk ke testedValue. Intinya testedValues = produced Values

        // jika ada yang tidak sama, maka dikasi status fail. Value yang tidak sama akan di pisah;
        if (!in_array($value, $nominalValues)) {
          $status = 'fail';
          $failValues[] = $value;
        } else {
          $successValues[] = $value;
        }
      }

      if (in_array($applicPropertyIdent, ['SERIALNUMBER', 'Serialnumber', 'serialnumber', 'serialNumber', 'SerialNumber', 'SERIAL_NUMBER', 'Serial_umber', 'serial_number', 'serial_Number', 'Serial_Number'])) {
        $translator = function ($values) use ($keepOneByOne, $pattern) {
          // ubah keep nya jika ingin oneByOne atau tidak
          $oneByOne = false;
          $length = count($values);
          $s = [];
          $i = 0;
          $span = '-';
          while (isset($values[$i])) {
            $s[] = $values[$i];
            if ($keepOneByOne and ($i < $length - 1)) $s[] = ', ';

            if (
              isset($values[$i + 1]) and
              (($values[$i + 1] - $values[$i]) >= 1)
            ) {
              if ((count($s) > 1) and !$oneByOne) {
                array_pop($s);
                if ($keepOneByOne) $s[] = ', ';
                $oneByOne = false;
              } else {
                // $keepOneByOne ? null : ($s[] = ' through ');
                $keepOneByOne ? null : ($s[] = $span);
              }
              if (($values[$i + 1] - $values[$i]) >= 2) {
                if (!$keepOneByOne) $s[] = $values[$i];
                if (!$keepOneByOne) $s[] = ', ';
                $oneByOne =  true;
              } else {
                $oneByOne = ($keepOneByOne) ? true : false;
              }
            }
            $i++;
          }
          foreach ($s as $k => $v) {
            if ($v === $span) {
              // maksudnya jika N011 ~ N012, akan diganti menjadi N011, N012 (ganti tilde pakai comma);
              if (abs($s[$k + 1] - $s[$k - 1]) === 1) {
                $s[$k] = ', ';
              }
              // maksudnya jika N011 ~ N011, akan diganti menjadi N011, (hapus salah satu karena sama-sama N011);
              elseif (abs($s[$k + 1] - $s[$k - 1]) === 0) {
                $s[$k] = '';
                $s[$k + 1] = '';
              }
            }
          }
          if ($pattern) {
            $regex = "/.*(\(.*\)).*/"; // akan match dengan yang didalam kurungnya /N(219)/ akan match dengan 219
            preg_match_all($regex, $pattern, $structure, PREG_SET_ORDER, 0);
          }
          foreach ($s as $n => $v) {
            if (!is_string($v)) {
              $s[$n] = sprintf('%03d', $s[$n]);
              if ($pattern) {
                if ($structure) {
                  $newValue = str_replace($structure[0][1], $s[$n], $structure[0][0]); // $newValue = "/N001/"
                  $newValue = trim($newValue);
                  $newValue = substr_replace($newValue, "", 0, 1); // delete "/" di depan
                  $newValue = substr_replace($newValue, "", strlen($newValue) - 1, 1); // delete "/" dibelakang
                  $s[$n] = $newValue;
                }
              }
            }
          }
          $s = (join("", $s));
          return $s;
        };
        $s = $translator($successValues);
        if ($status === 'fail') {
          $testedValues['%MESSAGE'] = "ERROR: '$applicPropertyIdent' only contains $s and does not contains such ";
          $s = $translator($failValues);
          $testedValues['%MESSAGE'] .= $s;
          $testedValues['text'] = '';
        } else {
          $testedValues['text'] = $s;
        }
      } else {
        $r = join(", ", $successValues);
        $testedValues['text'] = $r;
        if ($status === 'fail') {
          $r = join(", ", $failValues);
          $testedValues['%MESSAGE'] = "ERROR: For '$applicPropertyIdent' does not contains such $r";
        }
      }

      if ($useDisplayName and $status === 'success') {
        $testedValues['text'] = $displayName ? ($displayName . ": " . $testedValues['text']) : $testedValues['text'];
      }
      if ($status === 'fail') {
        $testedValues['text'] = !empty($assert->textContent) ? $assert->textContent : $testedValues['text'];
        $status = empty($testedValues['text']) ? $status : 'success';
      }
      $testedValues['%STATUS'] = $status;
      $testedValues['%APPLICPROPERTYTYPE'] = $applicPropertyType;
      $testedValues['%APPLICPROPERTYIDENT'] = $applicPropertyIdent;
      $testedValues['%APPLICPROPERTYVALUES'] = $applicPropertyValues;
    }
    // $ret = array($applicPropertyIdent => $testedValues);
    // return $ret;
    // dump($testedValues);
    return $testedValues;
  }

  /**
   * DEPRECIATED. Dipindah ke \CSDBObject class
   * @return array ['text' => string, 'andOr' => String, 'children' => array contain evaluated child]
   */
  public static function test_evaluate(\DOMElement $evaluate, $ACTdoc = null, $CCTdoc = null, $PCTdoc = null, bool $keepOneByOne = false, $useDisplayName = true)
  {
    $children = self::children($evaluate);
    foreach ($children as $child) {
      $resolved[] = self::resolver_childApplic($child, $ACTdoc, $CCTdoc, null, $keepOneByOne, $useDisplayName);
    }
    $andOr = $evaluate->getAttribute('andOr');
    $text = '';
    $loop = 0;
    $failtext = '';

    if ($andOr === 'and') {
      $isFail = array_filter($resolved, (fn ($r) => isset($r['%STATUS']) and $r['%STATUS']  === 'fail' ? $r : false));
      if (!empty($isFail)) {
        return ['text' => '', 'andOr' => $andOr, 'children' => $resolved];
      }
    }

    $evaluatedElement = [];
    while (isset($resolved[$loop])) {
      if (count($resolved) > 2) {
        $separator = isset($resolved[$loop + 1]) ? ', ' : ", {$andOr} ";
      } else {
        $separator = " {$andOr} ";
      }

      if ($andOr === 'or') {
        if ($resolved[$loop]['%STATUS'] === 'success') {
          if ($resolved[$loop]['text']) {
            $text .= $separator . $resolved[$loop]['text'];
          }
        } else {
          if ($resolved[$loop]['text']) {
            $failtext .= $separator . $resolved[$loop]['text'];
          }
        }
      } else {
        $text .= $separator . $resolved[$loop]['text'];
      }
      $evaluatedElement[] = $resolved[$loop];
      $loop++;
    }

    $text = ltrim($text, $separator);
    return ['text' => '(' . $text . ')', 'andOr' => $andOr, 'children' => $evaluatedElement];
  }

  /**
   * untuk mendapatkan child element
   * @param \DOMElement $element
   * @param array $exclude
   * @return array
   */
  public static function children(\DOMElement $element, array $excludeElement = [])
  {
    $arr = [];
    $element = $element->firstElementChild;
    if ($element) {
      if (!in_array($element->tagName, $excludeElement)) {
        $arr[] = $element;
      }
      while ($element = $element->nextElementSibling) {
        if (!in_array($element->tagName, $excludeElement)) {
          $arr[] = $element;
        }
      }
    }
    return $arr;
  }

  /**
   * sementara ini hanya bisa mencari attribute pada dmCode, dmlCode, pmCode, infoEntityIdent Code
   */
  public static function get_attribute_from_filename(string $filename, string $attributeName): string
  {
    $decoded = CSDBStatic::decode_ident($filename);
    switch ($decoded['prefix']) {
      case 'DMC-':
        return $decoded['dmCode'][$attributeName];
      case 'PMC-':
        return $decoded['pmCode'][$attributeName];
      case 'DML-':
        return $decoded['dmlCode'][$attributeName];
      case 'ICN-':
        return $decoded['infoEntityIdent'][$attributeName];
      default:
        return '';
    }
  }

  protected static $footnoteSymMarkers = ['*', '†', '‡', '§', '¶', '#', '♠', '♥', '◆', '♣'];
  // protected static $footnoteSymMarkers = ['&#42;', '&#8224;', '&#8225;', '&#167;', '&#182;', '#', '&#9824;', '&#9829;', '&#9830;', '&#9827;'];

  /**
   * minimum position is 1;
   * alpha character is limited to a thru z
   * symbol character is limited to 10 position
   */
  public static function get_footnote_mark(int $position, string $markType)
  {
    if (!$markType) $markType = 'num';

    switch ($markType) {
      case 'num':
        return (string)$position;
      case 'alpha':
        return (string)range('a', 'z')[$position - 1];
      case 'sym':
        return (string)self::$footnoteSymMarkers[$position - 1];
      default:
        return '';
    }
  }

  public static function isJsonString(mixed $string)
  {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
  }

  /**
   * diambil dari @test_assert di CSDBObject.php
   * breakApplicPropertyValues()
   * $applicPropertyValues = "N071|N001N005`N010|N015throughN020|N020|N030~N035|N001~N005~N010";
   * $regex[0] untuk match ->N030~N035<- ->N001~N005~N010<-
   * $regex[1] untuk match ->N071<- ->N015throughN020<- ->N020<-
   * semua value yang akan di cek terhadap @valuePattern (jika @valueDataType is string) ada dalam match-group ke 1(index ke 1) atau 2 atau 3
   * jika range (tilde) maka $start = group 1; $end = group 2
   * jika singe value maka group 3
   */
  public static function range(string $applicPropertyValues, string $pattern = '', string $valueDataType = '')
  {
    $values_generated = array();
    $regex = ["([A-Za-z0-9\-\/]+)~([A-Za-z0-9\-\/]+)(?:[~`!@#$%^&*()\-_+={}\[\]\\;:'" . '",<.>\/? A-Za-z0-9]+)*', "|", "(?<![`~!@#$%^&*()-_=+{}\[\]\\;;'" . '",<.>\/? ])([A-Za-z0-9\-\/]+)(?![`~!@#$%^&*()-_=+{}\[\]\\;;' . "',<.>\/? ])"]; // https://regex101.com/r/vKhlJB/3 account ferdisaptoko@gmail.com
    $regex = "/" . implode($regex) . "/";
    preg_match_all($regex, $applicPropertyValues, $matches, PREG_SET_ORDER, 0); // matches1 = "N003~N005", matches2 = "N010~N015"
    foreach ($matches as $values) {
      // get start value for iterating
      $start = null;
      $end = null;
      $singleValue = null;
      if ($valueDataType != 'string') {
        $start = $values[1];
        $end = $values[2];
        $singleValue = (isset($values[3]) and $values[3]) ? $values[3] : null;
      } else {
        if (!empty($pattern)) { // jika mau di iterate
          preg_match_all($pattern, $values[1], $matches, PREG_SET_ORDER);
          $start = isset($matches[0][0]) ? $matches[0][1] : null;
          preg_match_all($pattern, $values[2], $matches, PREG_SET_ORDER);
          $end = isset($matches[0][0]) ? $matches[0][1] : null;
          if ((isset($values[3]) and $values[3])) {
            preg_match_all($pattern, $values[2], $matches, PREG_SET_ORDER);
            $singleValue = isset($matches[0][0]) ? $matches[0][1] : null;
          }
        }
      }
      if ($start and $end) {
        $l = strlen($start);
        if ($l !== strlen((int)$start)) {
          try {
            str_increment($start);
            str_decrement($start); // agar jumlah digit / strlen nya sama
          } catch (\Throwable $e) {
            $start++;
            $start--;
          }
          while ($start <= $end) {
            // $values_generated[] = $start;
            $values_generated[] = str_pad($start, $l, '0', STR_PAD_LEFT);
            $start++;
          };
        } else {
          $range = range($start, $end);
          foreach ($range as $v) ($values_generated[] = str_pad($v, $l, '0', STR_PAD_LEFT));
        }
      }
      if ($singleValue) {
        $values_generated[] = $singleValue;
      }
    }
    return $values_generated;
  }

  /**
   * ending is relativePath + '/'
   * digunakan di Fop\Pdf
   */
  public static function getRelativePath(string $from, string $to): string
  {
    // some compatibility fixes for Windows paths
    $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
    $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
    $from = str_replace('\\', '/', $from);
    $to   = str_replace('\\', '/', $to);

    $from     = explode('/', $from);
    $to       = explode('/', $to);
    $relPath  = $to;

    foreach ($from as $depth => $dir) {
      // find first non-matching dir
      if ($dir === $to[$depth]) {
        // ignore this directory
        array_shift($relPath);
      } else {
        // get number of remaining dirs to $from
        $remaining = count($from) - $depth;
        if ($remaining > 1) {
          // add traversals up to first matching dir
          $padLength = (count($relPath) + $remaining - 1) * -1;
          $relPath = array_pad($relPath, $padLength, '..');
          break;
        } else {
          $relPath[0] = './' . $relPath[0];
        }
      }
    }
    return implode('/', $relPath);
  }
}
