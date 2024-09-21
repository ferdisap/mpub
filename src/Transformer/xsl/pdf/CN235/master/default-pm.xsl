<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" xmlns:fox="http://xmlgraphics.apache.org/fop/extensions" xmlns:php="http://php.net/xsl">

  <!-- 
    pt01 = component maintenance
    pt02 = ipd
    pt03 = service bulletin,
    sementara baru pt03 dulu
  -->

  <xsl:template name="pageMasterByPt">
    <xsl:param name="masterName" select="$masterName"/>
    
    <fo:layout-master-set>
      <!-- jika not(pm/@pt) maka akan pakai default-pm, see Main.xsl -->
      
      <!-- 
        Jadi, semua page-sequence-master dan simple-page-master akan dikumpulkan/ditulis semua di xsl.fo.
        Ini akan membuat file xsl.fo menjadi besar, tapi belum ada solusinya.
      -->
      
      <!-- masterName: default-pm -->
      <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::add_masterName', $masterName)"/>
      <xsl:call-template name="get_simplePageMaster">
        <xsl:with-param name="masterName" select="$masterName"/>
      </xsl:call-template>
      <xsl:call-template name="get_pageSequenceMaster">
        <xsl:with-param name="masterName" select="$masterName"/>
      </xsl:call-template>
      <!-- <xsl:if test="$masterName= 'maintPlanning'">
      </xsl:if> -->

      <!-- scan pmRef disetiap PMC -->
      <xsl:for-each select="//pmRef">
        <xsl:variable name="pmDoc" select="php:function(
        'Ptdi\Mpub\Main\CSDBStatic::document',
        $csdb_path,
        php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmIdent', descendant::pmRefIdent)
        )"/>
        <xsl:variable name="pt" select="string($pmDoc/pm/@pmType)"/>
        <xsl:variable name="masterName" select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','pmType', string($pt), 'abbreviation', 'default')" />
        <xsl:variable name="checkMasterName" select="boolean(php:function('Ptdi\Mpub\Transformer\Pdf::add_masterName', $masterName))"/>
        
        <xsl:choose>
          <xsl:when test="$checkMasterName">
            <xsl:call-template name="get_simplePageMaster">
              <xsl:with-param name="masterName" select="$masterName"/>
            </xsl:call-template>
            <xsl:call-template name="get_pageSequenceMaster">
              <xsl:with-param name="masterName" select="$masterName"/>
            </xsl:call-template>
          </xsl:when>
          <xsl:otherwise>
            <!-- nothing to do. Artinya mastername sudah ditambahkan dan tidak akan ada multiple masterName di layout -->
          </xsl:otherwise>
        </xsl:choose>
      </xsl:for-each>
    </fo:layout-master-set>
  </xsl:template>

</xsl:transform>
