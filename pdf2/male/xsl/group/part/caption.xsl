<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <!-- NOTE
    Untuk hasil yang baik, 
    1. ada buat <colspec> untuk setiap column di <captionGroup>, lengkap dengan @colname dan @colwidth nya!
       atau tidak ada semua colspec
    2. semua <captionEntry memiliki colname!
    3. belum bisa mengakomodir @valign
    4. Jika pakai @captionWidth di <caption>, pastikan kurang dari @colwidth di <colsep>. 
       Batasnya yaitu jika pakai @colsep dan @rosep (di <captionGroup>), cellpadding akan ditambah 1mm (LTRB).
       setiap <td> juga akan otomatis tambah padding 1mm++ (LTRB).
       jadi @captionWidth < @colwidth = += 4-5mm
  -->

  <xsl:template match="captionGroup">
    <table>
      <xsl:if test="@colsep and @rowsep">
        <xsl:attribute name="cellpadding">1mm</xsl:attribute>
      </xsl:if>

      <xsl:call-template name="cgmark"/>
      <tbody>
        <xsl:for-each select="captionBody/captionRow">
          <xsl:if test="ancestor::captionGroup/@cols >= count(captionEntry)">
            <tr>
              <xsl:call-template name="cgmark"/>
              <xsl:for-each select="captionEntry">
                <td>
                  <xsl:call-template name="tdstyle"/>                
                  <xsl:call-template name="colspan"/>
                  <xsl:call-template name="rowspan"/>
                  <xsl:apply-templates/>
                </td>
              </xsl:for-each>
            </tr>
          </xsl:if>
        </xsl:for-each>
      </tbody>
    </table>
  </xsl:template>

  <xsl:template name="tdstyle">
    <xsl:attribute name="style">
      <xsl:call-template name="colsep"/>
      <xsl:call-template name="rowsep"/>
      <xsl:call-template name="colwidth"/>
      <xsl:call-template name="alignCaptionEntry"/>
    </xsl:attribute>
  </xsl:template>

  <xsl:template name="colsep">
    <xsl:variable name="colsep">
      <xsl:if test="ancestor::captionGroup/@colsep = '1'">
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

  <xsl:template name="rowsep">
    <xsl:variable name="rowsep">
      <xsl:if test="ancestor::captionGroup/@rowsep = '1'">
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

  <xsl:template name="colwidth">
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
      <xsl:variable name="units" select="php:function('preg_replace', '/[0-9]+/' ,'', string(ancestor::tgroup/colspec[1]/@colwidth))"/>
      <!-- jika di entry ada @spanname, dan di ancestor ada @colname, dan di ancestor ada @colwidth  -->
      <xsl:if test="$spanname and ancestor::captionGroup/colspec[@colname = $colname] and ancestor::captionGroup/colspec[@colname = $colname]/@colwidth">
        <xsl:variable name="spanspec" select="ancestor::captionGroup/spanspec[@spanname = $spanname]"/>
        <xsl:variable name="int_namest" select="number(substring($spanspec/@namest/.,4))"/>

        <xsl:text>width:</xsl:text>
        <xsl:call-template name="getWidthByColspec">
          <xsl:with-param name="int_namest" select="$int_namest"/>
          <xsl:with-param name="nameend" select="$spanspec/@nameend"/>
        </xsl:call-template><xsl:value-of select="$units"/>
        <xsl:text>;</xsl:text>
      </xsl:if>
      
      <!-- jika di entry TIDAK ada @spanname, dan di ancestor ADA @colname @colwidth -->
      <xsl:if test="not($spanname) and ancestor::captionGroup/colspec[@colname = $colname]/@colwidth">
        <xsl:text>width:</xsl:text>
        <xsl:value-of select="ancestor::captionGroup/colspec[@colname = $colname]/@colwidth"/>
        <xsl:text>;</xsl:text>
      </xsl:if>
    </xsl:variable>

    <xsl:value-of select="$width"/>
  </xsl:template>

  <xsl:template name="getWidthByColspec">
    <xsl:param name="tmp_width">0</xsl:param>
    <xsl:param name="int_namest"/>
    <xsl:param name="nameend"/>
    <xsl:variable name="colname" select="concat('col', $int_namest)"/>
    <!-- jika di ancestor ada @colname dan @colwidth -->
    <xsl:if test="ancestor::captionGroup/colspec[@colname = $colname] and ancestor::captionGroup/colspec[@colname = $colname]/@colwidth">
      <xsl:variable name="colspec" select="ancestor::captionGroup/colspec[@colname = $colname]"/>
      <xsl:variable name="value" select="number($tmp_width) + php:function('preg_replace', '/[^0-9]+/', '', string($colspec/@colwidth))"/>
      <xsl:if test="not($colspec/@colname[. = $nameend])">
        <xsl:call-template name="getWidthByColspec">
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
    <xsl:if test="ancestor::captionGroup/colspec[@colname = $colname] and not(ancestor::captionGroup/colspec[@colname = $colname]/@colwidth)">
      <xsl:value-of select="$tmp_width"/>
    </xsl:if>
  </xsl:template>

  <xsl:template name="colspan">
    <xsl:param name="spanname" select="@spanname"/>

    <xsl:if test="ancestor::captionGroup/spanspec[@spanname = $spanname]">
      <xsl:variable name="spanspec" select="ancestor::captionGroup/spanspec[@spanname = $spanname]"/>
      <xsl:variable name="int_namest" select="number(substring($spanspec/@namest/.,4))"/>
      <xsl:variable name="int_nameend" select="number(substring($spanspec/@nameend/.,4))"/>

      <xsl:attribute name="colspan">
        <xsl:value-of select="number($int_nameend) - number($int_namest) + 1"/>  
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template name="rowspan">
    <xsl:if test="@morerows">
      <xsl:attribute name="rowspan"><xsl:value-of select="@morerows"/></xsl:attribute>
    </xsl:if>
  </xsl:template>

  <xsl:template name="alignCaptionEntry">
    <xsl:param name="alignCaptionEntry"><xsl:value-of select="@alignCaptionEntry"/></xsl:param>
    <xsl:choose>
      <xsl:when test="$alignCaptionEntry = 'left'">text-align:L;</xsl:when>
      <xsl:when test="$alignCaptionEntry = 'right'">text-align:R;</xsl:when>
      <xsl:otherwise>text-align:C;</xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template match="caption">
    <!-- caption masih  belum bisa pakai calign=B jika ada 2 caption yang memiliki height dalam satu line. $this->y akan turun kebawah-->
    &#160;<span captionline="true" style="font-weight:bold" calign="T">
    <!-- &#160;<span captionline="true" style="font-weight:bold" calign="B"> -->
      <xsl:call-template name="cgmark"/>

      <xsl:if test="@captionWidth">
        <xsl:attribute name="width"><xsl:value-of select="@captionWidth"/></xsl:attribute>
      </xsl:if>

      <xsl:if test="@captionHeight">
        <xsl:attribute name="height"><xsl:value-of select="@captionHeight"/></xsl:attribute>
      </xsl:if>

      <xsl:call-template name="color"/>

      <xsl:text> </xsl:text>
      <xsl:apply-templates select="captionLine"/>
      <xsl:text> </xsl:text>

    </span>&#160;
  </xsl:template>

  <!-- belum selesai -->
  <xsl:template name="color">
    <xsl:variable name="amber">266,193,7</xsl:variable>
    <xsl:variable name="black">0,0,0</xsl:variable>
    <xsl:variable name="green">20,163,57</xsl:variable>
    <xsl:variable name="grey">128,128,128</xsl:variable>
    <xsl:variable name="red">255,0,0</xsl:variable>
    <xsl:variable name="white">255,255,255</xsl:variable>
    <xsl:variable name="yellow">255,255,0</xsl:variable>
    <xsl:choose>
      <xsl:when test="@color = 'co01'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$green"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co02'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$amber"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co03'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$yellow"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co04'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$red"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co07'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$white"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co08'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$grey"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co62'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$yellow"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$white"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co66'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$red"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co67'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$red"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$white"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co81'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$black"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$yellow"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co82'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$black"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$white"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co83'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$black"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$red"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co84'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$black"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$green"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co85'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$black"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$amber"/></xsl:attribute>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>