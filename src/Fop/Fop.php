<?php

namespace Ptdi\Mpub\Fop;

// use FilePathNormalizer;
// use FilePathNormalizer\FilePathNormalizer as FilePathNormalizerFilePathNormalizer;

/**
 * ada package PHP yang bisa menjalankan .jar, .class. Refer to github.com/php-java/php-java, tapi masih development
 * Nanti itu dicoba. Sementara ini komunikasi ke java pakai shell saja dulu
 * 
 * PDF file akan di write ke disk, kemudian akan dihapus setelah di ambil
 * ini akan lama karena pdf yang sudah dibuat akan diwrite ke disk oleh java, kemudian diread (load) kembali oleh ram
 */

class Fop
{
  public static bool $autodelete = true;
  protected static string $outputPath = '';
  /**
   * jangan ada space di path, dan harus absolute path
   * @return string pdf content machine-readable
   * @return bool if fail to ctransform
   */
  public static function FO_to_PDF(string $inputName, string $outputName = '')
  {
    if($outputName === ''){
      $outputName = __DIR__. DIRECTORY_SEPARATOR. 'generated'. DIRECTORY_SEPARATOR. rand(100,999) . 'pdf';
      self::$autodelete = true;
    }
    // tidak perlu lagi casting windows path to unix path
    $inputName = self::getRelativePath(__DIR__, $inputName);
    $outputName = self::getRelativePath(__DIR__, $outputName);

    chdir(__DIR__);
    shell_exec("fop -c conf/fop.xconf -fo $inputName -pdf $outputName");
    
    try {
      $output = file_get_contents($outputName);
      if(self::$autodelete) unlink($outputName); // hapus file dari storage di sini
      return $output;
    } catch (\Throwable $th) {
      return false;
    }
    return false;
  }

  /**
   * ending is relativePath + '/'
   */
  private static function getRelativePath($from, $to): string
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

  // private function findOverlap($str1, $str2){
  //   $return = array();
  //   $sl1 = strlen($str1);
  //   $sl2 = strlen($str2);
  //   $max = $sl1>$sl2?$sl2:$sl1;
  //   $i=1;
  //   while($i<=$max){
  //     $s1 = substr($str1, -$i);
  //     $s2 = substr($str2, 0, $i);
  //     dump($s1 . ' | '. $s2);
  //     if($s1 == $s2){
  //       $return[] = $s1;
  //     }
  //     $i++;
  //   }
  //   if(!empty($return)){
  //     return $return;
  //   }
  //   dd($return);
  //   return false;
  // }

  // /**
  //  * $this->replaceOverlap("abxcdex", "xcdexfg") // "abxcdexfg" 
  //  */
  // private function replaceOverlap($str1, $str2, $length = "long"){
  //   if($overlap = $this->findOverlap($str1, $str2)){
  //     switch($length){
  //       case "short":
  //         $overlap = $overlap[0];
  //         break;
  //       case "long":
  //       default:
  //         $overlap = $overlap[count($overlap)-1];
  //         break;
  //     }     
  //     $str1 = substr($str1, 0, -strlen($overlap));
  //     $str2 = substr($str2, strlen($overlap));
  //     return $str1.$overlap.$str2;
  //   }
  //   return false;
  // }

  /**
   * untuk mengubah windows path menjadi unix path
   * tapi saat dipakai ke FOP, fail jika pathnya ada character spasi
   */
  // public function wp_normalize_path( string $path ) {
  //   $path = str_replace( '\\', '/', $path );
  //   $path = preg_replace( '|(?<=.)/+|', '/', $path );
  //   if ( ':' === substr( $path, 1, 1 ) ) {
  //       $path = ucfirst( $path );
  //   }
  //   return $path;
  // }
}
