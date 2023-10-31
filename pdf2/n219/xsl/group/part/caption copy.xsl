<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <!-- di library ietp_n219, attribute @cols (M) pada elemen <captionGroup> belum applied  -->

  <xsl:template match="captionGroup">
    <table>
      <xsl:apply-templates select="captionBody"/>
    </table>
  </xsl:template>

  <xsl:template match="captionBody">
    <tbody>
      <xsl:apply-templates select="captionRow"/>
    </tbody>
  </xsl:template>

  <xsl:template match="captionRow">
    <xsl:variable name="qtyColMust">
      <xsl:value-of select="ancestor::captionGroup/@cols"/>
    </xsl:variable>
    <xsl:if test="$qtyColMust >= count(captionEntry)">
      <tr style="border:1px solid red">
        <xsl:apply-templates select="captionEntry"/>
      </tr>
    </xsl:if>
  </xsl:template>

  <xsl:template match="captionEntry">
    <xsl:param name="spanname"><xsl:value-of select="@spanname"/></xsl:param>
    <xsl:param name="colname"><xsl:value-of select="@colname"/></xsl:param>
    <td>
      <xsl:variable name="style">
        <xsl:call-template name="alignCaptionEntry"/>
      </xsl:variable>
      <xsl:variable name="width"/>

      <xsl:choose>
        <xsl:when test="@spanname and @colname = ancestor::captionGroup/spanspec[@spanname = $spanname]/@namest">
          <xsl:variable name="colspan">
            <xsl:call-template name="getColspan">
              <xsl:with-param name="namest" select="ancestor::captionGroup/spanspec[@spanname = $spanname]/@namest"/>
              <xsl:with-param name="nameend" select="ancestor::captionGroup/spanspec[@spanname = $spanname]/@nameend"/>
            </xsl:call-template>
          </xsl:variable>
          <xsl:attribute name="colspan"><xsl:value-of select="$colspan"/></xsl:attribute>
          <xsl:attribute name="style">
            <xsl:value-of select="$style"/>
            <xsl:if test="2 >= $colspan">
              <!-- asumsinya colwidth antar td(colname) itu sama -->
              <xsl:variable name="colwidth"><xsl:call-template name="colwidth"/></xsl:variable>
              <xsl:value-of select="$colwidth * $colspan"/>
            </xsl:if>
          </xsl:attribute>
        </xsl:when>        

        <xsl:when test="@namest and @nameend">
          <xsl:variable name="colspan">
            <xsl:call-template name="getColspan">
              <xsl:with-param name="namest" select="@namest"/>
              <xsl:with-param name="nameend" select="@nameend"/>
            </xsl:call-template>
          </xsl:variable>
          <xsl:attribute name="colspan"><xsl:value-of select="$colspan"/></xsl:attribute>
          <xsl:attribute name="style">
            <xsl:value-of select="$style"/>
            <xsl:if test="2 >= $colspan">
              <!-- asumsinya colwidth antar td(colname) itu sama -->
              <xsl:variable name="colwidth"><xsl:call-template name="colwidth"/></xsl:variable>
              <xsl:value-of select="$colwidth * $colspan"/>
            </xsl:if>
          </xsl:attribute>
        </xsl:when>              
      </xsl:choose>
      

      <xsl:if test="@morerows">
        <xsl:attribute name="rowspan"><xsl:value-of select="@morerows"/></xsl:attribute>
      </xsl:if>

      
      <!-- <xsl:attribute name="style"> -->
        <!-- <xsl:value-of select="$style"/> -->
        <!-- valign belum bisa di tcpdf -->
        <!-- <xsl:call-template name="valign"/> -->
        <!-- tidak perlu ditambah call/apply tempalte colwidth untuk set width -->
        <!-- <xsl:call-template name="colwidth"/> -->
        <!-- <xsl:if test="$colspan > 0">
          <xsl:call-template name="colwidth"/>
        </xsl:if> -->        
      <!-- </xsl:attribute> -->

      <xsl:apply-templates/>
    </td>
  </xsl:template>
  
  <xsl:template name="colwidth">
    <xsl:param name="colname"><xsl:value-of select="@colname"/></xsl:param>
    <xsl:if test="@colname and @colname = ancestor::captionGroup/colspec[@colname = $colname]/@colname">
      <xsl:text>width:</xsl:text><xsl:value-of select="ancestor::captionGroup/colspec[@colname = $colname]/@colwidth"/>
    </xsl:if>
  </xsl:template>

  <xsl:template name="getColspan">
    <xsl:param name="namest">foo</xsl:param>
    <xsl:param name="nameend">bar</xsl:param>

    <xsl:variable name="namestPos">
      <xsl:for-each select="//captionGroup/colspec">
        <xsl:if test="@colname = $namest">
          <xsl:number/>
        </xsl:if>
      </xsl:for-each>
    </xsl:variable>
    <xsl:variable name="nameendPos">
      <xsl:for-each select="//captionGroup/colspec">
        <xsl:if test="@colname = $nameend">
          <xsl:number/>
        </xsl:if>
      </xsl:for-each>
    </xsl:variable>

    <xsl:value-of select="number($nameendPos) - number($namestPos) + 1"/>

  </xsl:template>


  <xsl:template name="alignCaptionEntry">
    <xsl:param name="alignCaptionEntry"><xsl:value-of select="@alignCaptionEntry"/></xsl:param>
    <xsl:choose>
      <xsl:when test="$alignCaptionEntry = 'left'">text-align:L;</xsl:when>
      <xsl:when test="$alignCaptionEntry = 'right'">text-align:R;</xsl:when>
      <xsl:otherwise>text-align:C;</xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:template name="valign">
    <!-- <xsl:param name="valign"><xsl:value-of select="@valign"/></xsl:param>
    <xsl:choose>
      <xsl:when test="$valign = 'middle'"></xsl:when>
    </xsl:choose> -->
  </xsl:template>
  
  <xsl:template match="caption">
    &#160;<span captionline="true" style="font-weight:bold" calgin="B">
      <xsl:call-template name="cgmark"/>

      <xsl:if test="@captionWidth">
        <xsl:attribute name="width"><xsl:value-of select="@captionWidth"/></xsl:attribute>
      </xsl:if>

      <xsl:if test="@captionHeight">
        <xsl:attribute name="height"><xsl:value-of select="@captionHeight"/></xsl:attribute>
      </xsl:if>

      <xsl:call-template name="color"/>

      <xsl:apply-templates select="captionLine"/>

    </span>&#160;
  </xsl:template>

  <xsl:template match="captionText">
    <xsl:apply-templates/>
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