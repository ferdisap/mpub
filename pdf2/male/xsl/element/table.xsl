<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <xsl:template match="table">
    <xsl:variable name="title">
      <xsl:call-template name="tableTitle"/>
    </xsl:variable>
    <xsl:apply-templates select="tgroup">
      <xsl:with-param name="title" select="$title"/>
    </xsl:apply-templates>
    
  </xsl:template>

  <!-- 
    jika ingin ada footnote dan bisa ref, 
    1. tulis footnote setelah text yang ingin ditambahkan
    2. tambahkan id di tag <footnote> 
    3. pakai <footnoteRef> jika para lain ingin ref ke footnote ini-->

  <!-- 
    table tidak bisa ditaruh di center div
   -->
  <xsl:template match="tgroup">
    <xsl:param name="title"/>
    <xsl:variable name="footnote" select="descendant::footnote"/>
    <xsl:variable name="qtyTgroup">
      <xsl:value-of select="count(parent::table/tgroup)"/>
    </xsl:variable>

    <div>
    <!-- <div style="page-break-inside:avoid;"> -->
      <xsl:for-each select="parent::table">
        <xsl:call-template name="cgmark" select="."/>
      </xsl:for-each>
      <table style="text-align:left;">
        <xsl:attribute name="cellpadding">
          <xsl:choose>
            <xsl:when test="@tgstyle">
              <xsl:variable name="tgstyle"><xsl:value-of select="@tgstyle"/></xsl:variable>
              <xsl:value-of select="php:function('Ptdi\Mpub\Pdf2\male\DMC_male::getTableStyle', $tgstyle, 'cellpadding')"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:text>1mm</xsl:text>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <thead>
          <xsl:apply-templates select="thead/row"/>
        </thead>
        <tbody>
          <xsl:apply-templates select="tbody/row"/>
        </tbody>
        <tfoot>
          <xsl:apply-templates select="tfoot/row">
            <xsl:with-param name="userowsep" select="'no'"/>
            <xsl:with-param name="usemaxcolspan" select="'yes'"/>
          </xsl:apply-templates>
          <!-- <xsl:if test="$footnote">
            <tr>
              <td colspan="{number(ancestor-or-self::tgroup/@cols)}" style="line-height:0.1;border-top:1px solid black">&#160;</td>
            </tr>
            <xsl:for-each select="$footnote">
              <tr>
                <td colspan="{number(ancestor::tgroup/@cols)}"> 
                  <xsl:variable name="fnt" select="."/>
                  <xsl:for-each select="ancestor::table/descendant::footnote">
                    <xsl:if test="child::* = $fnt/child::*">
                      <span style="font-size:6">[<xsl:value-of select="position()"/>]&#160;<xsl:apply-templates select="$fnt"/></span>
                    </xsl:if>
                  </xsl:for-each>
                </td>
              </tr>
            </xsl:for-each>
          </xsl:if> -->
        </tfoot>
      </table>
      <xsl:if test="$footnote">
        <table style="border-top:1px solid black">
          <tbody>
            <tr><td style="line-height:0.5">&#160;</td></tr>
            <xsl:for-each select="$footnote">
              <xsl:variable name="fnt" select="."/>
              <xsl:for-each select="ancestor::table/descendant::footnote">
                <xsl:if test="child::* = $fnt/child::*">
                  <tr>
                    <td style="font-size:6;">
                      <table style="width:100%">
                        <tr>
                          <td style="width:5%">[<xsl:value-of select="position()"/>]&#160;</td>
                          <td style="width:95%"><xsl:apply-templates select="$fnt"/>&#160;</td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </xsl:if>
              </xsl:for-each>
            </xsl:for-each>
          </tbody>
        </table>
      </xsl:if>
      <xsl:if test="$title != ''">
        <br/>
        <br/>
        <div style="text-align:left">
          <span>
            <xsl:for-each select="ancestor::table/title">
              <xsl:call-template name="cgmark"/>
            </xsl:for-each>
          <xsl:value-of select="$title"/>
          <xsl:if test="$qtyTgroup > 1">
            <xsl:text>&#160;(sheet&#160;</xsl:text><xsl:number/>&#160;of&#160;<xsl:value-of select="$qtyTgroup"/><xsl:text>)</xsl:text>
          </xsl:if>
          </span>
        </div>
      </xsl:if>
    </div>
    <br/>
  </xsl:template>

  <xsl:template match="row">
    <xsl:param name="userowsep" select="'yes'"/>
    <xsl:param name="usemaxcolspan" select="'no'"/>
    <tr>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates select="entry">
        <xsl:with-param name="userowsep" select="$userowsep"/>
        <xsl:with-param name="usemaxcolspan" select="$usemaxcolspan"/>
      </xsl:apply-templates>
    </tr>
  </xsl:template>

  <xsl:template match="entry">
    <xsl:param name="userowsep" select="'yes'"/>
    <xsl:param name="usemaxcolspan" select="'yes'"/>
    <td>
      <xsl:call-template name="tb_tdstyle">
        <xsl:with-param name="userowsep" select="$userowsep"/>
      </xsl:call-template>
      <xsl:call-template name="tb_rowspan"/>
      <xsl:choose>
        <xsl:when test="$usemaxcolspan = 'yes'">
          <xsl:attribute name="colspan">
            <xsl:value-of select="ancestor::tgroup/@cols"/>
          </xsl:attribute>
        </xsl:when>
        <xsl:otherwise>
          <xsl:call-template name="tb_colspan"/>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates>
        <xsl:with-param name="usefootnote" select="'no'"/>
      </xsl:apply-templates>
    </td>
  </xsl:template>

  <xsl:template name="tb_tdstyle">
    <xsl:param name="userowsep" select="'yes'"/>
    <xsl:choose>
      <xsl:when test="ancestor::*[@tgstyle]">
        <xsl:call-template name="tdtgstyle">
          <xsl:with-param name="tgstyle" select="ancestor::*[@tgstyle]/@tgstyle"/>
          <xsl:with-param name="userowsep" select="$userowsep"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:attribute name="style">
          <xsl:if test="not($userowsep = 'no')">
            <xsl:call-template name="tb_rowsep"/>
          </xsl:if>
          <xsl:call-template name="tb_colsep"/>
          <xsl:call-template name="tb_colwidth"/>
          <xsl:call-template name="tb_alignCaptionEntry"/>
    
          <xsl:if test="ancestor::thead">
            <xsl:text>border-bottom:2px solid black;text-align:left</xsl:text>
          </xsl:if>
          <xsl:if test="ancestor::tfoot">
            <xsl:text>border-top:2px solid black;font-size:6;text-align:left</xsl:text>
          </xsl:if>
          <xsl:if test="ancestor::tbody">
            <xsl:text>text-align:justify;</xsl:text>
            <!-- <xsl:text>border:1px solid red</xsl:text> -->
          </xsl:if>
        </xsl:attribute>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="tb_rowsep">
    <xsl:variable name="rowsep">
      <xsl:if test="ancestor::tgroup/@rowsep = '1'">
        <xsl:value-of select="'1'"/>
      </xsl:if>
      <xsl:if test="@rowsep = '1'">
        <xsl:value-of select="'1'"/>
      </xsl:if>
    </xsl:variable>
    <xsl:if test="$rowsep = '1'">
      <xsl:text>border-top:0.5px dashed black;</xsl:text>
      <xsl:text>border-bottom:0.5px dashed black;</xsl:text>
    </xsl:if>
  </xsl:template>

  <xsl:template name="tb_colsep">
    <xsl:variable name="colsep">
      <xsl:if test="ancestor::tgroup/@colsep = '1'">
        <xsl:value-of select="'1'"/>
      </xsl:if>
      <xsl:if test="@colsep = '1'">
        <xsl:value-of select="'1'"/>
      </xsl:if>
    </xsl:variable>
    <xsl:if test="$colsep = '1'">
      <xsl:text>border-left:0.5px dashed black;</xsl:text>
      <xsl:text>border-right:0.5px dashed black;</xsl:text>
    </xsl:if>
  </xsl:template>

  <xsl:template name="tb_colspan">
    <xsl:param name="spanname" select="@spanname"/>
    <xsl:if test="ancestor::tgroup/spanspec[@spanname = $spanname]">
      <xsl:variable name="spanspec" select="ancestor::tgroup/spanspec[@spanname = $spanname]"/>
      <xsl:variable name="int_namest" select="number(substring($spanspec/@namest/.,4))"/> 
      <xsl:variable name="int_nameend" select="number(substring($spanspec/@nameend/.,4))"/>

      <xsl:attribute name="colspan">
        <xsl:value-of select="number($int_nameend) - number($int_namest) + 1"/>  
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template name="tb_rowspan">
    <xsl:if test="@morerows">
      <xsl:attribute name="rowspan"><xsl:value-of select="@morerows"/></xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template name="tb_colwidth">
    <xsl:param name="spanname" select="@spanname"/>
    <xsl:param name="colname">
      <xsl:if test="boolean(@colname)">
        <xsl:value-of select="@colname"/>
      </xsl:if>
      <xsl:if test="not(@colname)">
        <xsl:text>col</xsl:text><xsl:number/>
      </xsl:if>
    </xsl:param>
    <xsl:variable name="width">
      <!-- units mengambil dari colspec/@colwidth pertama -->
      <xsl:variable name="units" select="php:function('preg_replace', '/[0-9]+/' ,'', string(ancestor::tgroup/colspec[1]/@colwidth))"/>
      <!-- <xsl:value-of select="php:function('Ptdi\Mpub\Pdf2\male\DMC_male::dump',$units)"/> -->


      <!-- jika di entry ada @spanname, dan di ancestor ada @colname, dan di ancestor ada @colwidth  -->
      <xsl:if test="$spanname and ancestor::tgroup/colspec[@colname = $colname] and ancestor::tgroup/colspec[@colname = $colname]/@colwidth">
        <xsl:variable name="spanspec" select="ancestor::tgroup/spanspec[@spanname = $spanname]"/>
        <xsl:variable name="int_namest" select="number(substring($spanspec/@namest/.,4))"/>

        <xsl:text>width:</xsl:text>
        <xsl:variable name="tes">
          <xsl:call-template name="tb_getWidthByColspec">
            <xsl:with-param name="int_namest" select="$int_namest"/>
            <xsl:with-param name="nameend" select="$spanspec/@nameend"/>
          </xsl:call-template><xsl:value-of select="$units"/>
          <xsl:text>;</xsl:text>
        </xsl:variable>
        <!-- <xsl:value-of select="php:function('Ptdi\Mpub\Pdf2\male\DMC_male::dump',$tes)"/> -->
        <xsl:value-of select="$tes"/>
      </xsl:if>
      
      <!-- jika di entry TIDAK ada @spanname, dan di ancestor ADA @colname @colwidth -->
      <xsl:if test="not($spanname) and ancestor::tgroup/colspec[@colname = $colname]/@colwidth">
        <xsl:text>width:</xsl:text>
        <xsl:value-of select="ancestor::tgroup/colspec[@colname = $colname]/@colwidth"/>
        <xsl:text>;</xsl:text>
      </xsl:if>
    </xsl:variable>
    <xsl:value-of select="$width"/>
  </xsl:template>

  <xsl:template name="tb_getWidthByColspec">
    <xsl:param name="tmp_width">0</xsl:param>
    <xsl:param name="int_namest"/>
    <xsl:param name="nameend"/>
    <xsl:variable name="colname" select="concat('col', $int_namest)"/>
    <!-- jika di ancestor ada @colname dan @colwidth -->
    <xsl:if test="ancestor::tgroup/colspec[@colname = $colname] and ancestor::tgroup/colspec[@colname = $colname]/@colwidth">
    <xsl:variable name="colspec" select="ancestor::tgroup/colspec[string(@colname) = $colname]"/>
      <xsl:variable name="value" select="number($tmp_width) + php:function('preg_replace', '/[^0-9]+/', '', string($colspec/@colwidth))"/>
      <!-- <xsl:value-of select="php:function('Ptdi\Mpub\Pdf2\male\DMC_male::dump',$colspec)"/> -->
      <xsl:if test="not($colspec/@colname[. = $nameend])"> <!-- jika tidak sama dengan nameend, akan recursive sambil menambah width nya -->
        <xsl:call-template name="tb_getWidthByColspec">
          <xsl:with-param name="tmp_width" select="$value"/>
          <xsl:with-param name="int_namest" select="$int_namest + 1"/>
          <xsl:with-param name="nameend" select="$nameend"/>
        </xsl:call-template>
      </xsl:if>
      <xsl:if test="$colspec/@colname[. = $nameend]">
        <xsl:value-of select="$value"/>
      </xsl:if>
    </xsl:if>
    <!-- jika tidak ada coldwidth -->
    <xsl:if test="ancestor::tgroup/colspec[@colname = $colname] and not(ancestor::tgroup/colspec[@colname = $colname]/@colwidth)">
      <xsl:value-of select="$tmp_width"/>
    </xsl:if>
  </xsl:template>

  <xsl:template name="tb_alignCaptionEntry">
    <xsl:param name="alignCaptionEntry"><xsl:value-of select="@alignCaptionEntry"/></xsl:param>
    <xsl:choose>
      <xsl:when test="$alignCaptionEntry = 'left'">text-align:L;</xsl:when>
      <xsl:when test="$alignCaptionEntry = 'right'">text-align:R;</xsl:when>
      <xsl:otherwise>text-align:C;</xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="tableTitle">
    <xsl:variable name="current" select="."/>
    <xsl:variable name="title">
      <xsl:value-of select="title"/>
    </xsl:variable>
    <xsl:variable name="index">
      <xsl:for-each select="//table[./title]">
        <xsl:if test=". = $current">
          <xsl:value-of select="position()"/>
        </xsl:if>
      </xsl:for-each>
    </xsl:variable>

    <xsl:if test="$index != ''">
      <xsl:text>Table.&#160;</xsl:text>
      <xsl:value-of select="$index"/>&#160;<xsl:value-of select="$title"/>
    </xsl:if>
  </xsl:template>






























  <!-- ini nanti bisa dipisah, ini dipanggil di td -->
  <xsl:template name="tdtgstyle">
    <xsl:param name="userowsep" select="'yes'"/>
    <xsl:param name="tgstyle"/>

    <!-- alltdcenter -->
    <xsl:if test="$tgstyle = 'alltdcenter'">
      <xsl:attribute name="style">
        <xsl:if test="not($userowsep = 'no')">
          <xsl:call-template name="tb_rowsep"/>
        </xsl:if>
        <xsl:call-template name="tb_colsep"/>
        <xsl:call-template name="tb_colwidth"/>
        <xsl:call-template name="tb_alignCaptionEntry"/>
        
        <xsl:if test="ancestor::thead">
          <xsl:text>border-bottom:2px solid black;</xsl:text>
        </xsl:if>
        <xsl:if test="ancestor::tfoot">
          <xsl:text>border-top:2px solid black;font-size:6;text-align:left</xsl:text>
        </xsl:if>
        <xsl:if test="ancestor::tbody">
          <xsl:text>text-align:center;</xsl:text>
        </xsl:if>
      </xsl:attribute>
    </xsl:if>

    <!-- loa -->
    <xsl:if test="$tgstyle = 'loa'">
      <xsl:attribute name="style">
        <xsl:if test="not($userowsep = 'no')">
          <xsl:call-template name="tb_rowsep"/>
        </xsl:if>
        <xsl:call-template name="tb_colsep"/>
        <xsl:call-template name="tb_colwidth"/>
        <xsl:call-template name="tb_alignCaptionEntry"/>
        
        <xsl:text>text-align:left;</xsl:text>
        
      </xsl:attribute>
    </xsl:if>

    <!-- terminologies_notice -->
    <xsl:if test="$tgstyle = 'terminologies_notice'">
      <xsl:attribute name="style">
        <xsl:if test="not($userowsep = 'no')">
          <xsl:call-template name="tb_rowsep"/>
        </xsl:if>
        <xsl:call-template name="tb_colsep"/>
        <xsl:call-template name="tb_colwidth"/>
        <xsl:call-template name="tb_alignCaptionEntry"/>
  
        <xsl:variable name="pos"><xsl:number/></xsl:variable>

        <xsl:choose>
          <xsl:when test="number($pos) = 1">
            <xsl:text>text-align:left</xsl:text>
          </xsl:when>
          <xsl:otherwise>
            <xsl:text>text-align:justify</xsl:text>
          </xsl:otherwise>
        </xsl:choose>

      </xsl:attribute>
    </xsl:if>

    <!-- engine_limitation -->
    <xsl:if test="$tgstyle = 'engine_limitation'">
      <xsl:attribute name="style">
        <xsl:if test="not($userowsep = 'no')">
          <xsl:call-template name="tb_rowsep"/>
        </xsl:if>
        <xsl:call-template name="tb_colsep"/>
        <xsl:call-template name="tb_colwidth"/>
        <xsl:call-template name="tb_alignCaptionEntry"/>
  
        <xsl:variable name="pos"><xsl:number/></xsl:variable>

        <xsl:if test="ancestor::thead">
          <xsl:text>border-bottom:2px solid black;</xsl:text>
        </xsl:if>
        <xsl:if test="ancestor::tfoot">
          <xsl:text>border-top:2px solid black;font-size:6;text-align:left</xsl:text>
        </xsl:if>
        <xsl:if test="ancestor::tbody">
          <xsl:choose>
            <xsl:when test="$pos = 1">
              <xsl:text>text-align:left;</xsl:text>
            </xsl:when>
            <xsl:otherwise>
              <xsl:text>text-align:center;</xsl:text>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:if>
      </xsl:attribute>
    </xsl:if>

    <!-- gcs_limitation -->
    <!-- <xsl:if test="$tgstyle = 'engine_limitation'">
      <xsl:attribute name="style">
        <xsl:if test="not($userowsep = 'no')">
          <xsl:call-template name="tb_rowsep"/>
        </xsl:if>
        <xsl:call-template name="tb_colsep"/>
        <xsl:call-template name="tb_colwidth"/>
        <xsl:call-template name="tb_alignCaptionEntry"/>
  
        <xsl:variable name="pos"><xsl:number/></xsl:variable>

        <xsl:if test="ancestor::thead">
          <xsl:text>border-bottom:2px solid black;</xsl:text>
        </xsl:if>
        <xsl:if test="ancestor::tfoot">
          <xsl:text>border-top:2px solid black;font-size:6;text-align:left</xsl:text>
        </xsl:if>
        <xsl:if test="ancestor::tbody">
          <xsl:choose>
            <xsl:when test="$pos = 1">
              <xsl:text>text-align:left;</xsl:text>
            </xsl:when>
            <xsl:otherwise>
              <xsl:text>text-align:center;</xsl:text>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:if>
      </xsl:attribute>
    </xsl:if> -->
  </xsl:template>

</xsl:stylesheet>
