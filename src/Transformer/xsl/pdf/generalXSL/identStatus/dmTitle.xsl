<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template name="add_dmTitle">
    <xsl:param name="idParentBookmark"/>
    <xsl:param name="idBookmark" select="generate-id(.)"/>

    <xsl:variable name="chapter">
      <xsl:call-template name="get_chapter"/>
    </xsl:variable>

    <xsl:variable name="title">
      <xsl:value-of select="//dmAddressItems/dmTitle/techName" />
      <xsl:if test="//dmAddressItems/dmTitle/infoName">
        <xsl:text> - </xsl:text>
        <xsl:value-of select="//dmAddressItems/dmTitle/infoName" />
      </xsl:if>
    </xsl:variable>

    <fo:block>
      <xsl:call-template name="style-dmTitle"/>
      
      <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Pdf::fillBookmark', string($idBookmark), concat($chapter, ' ', $title), string($idParentBookmark))"/>

      <xsl:if test="$chapter">
        <fo:block margin-bottom="6pt">
          <xsl:value-of select="$chapter"/>
        </fo:block>
      </xsl:if>
      <fo:block>
        <xsl:value-of select="$title"/>
      </fo:block>
    </fo:block>
  </xsl:template>

  <xsl:template name="get_chapter">
    <xsl:param name="dmIdent"/>

    <xsl:variable name="systemCode">
      <xsl:choose>
        <xsl:when test="not($dmIdent)">
          <xsl:value-of select="number(//dmAddress/dmIdent/dmCode/@systemCode)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="number(descendant::dmCode/@systemCode)"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="subSystemCode">
      <xsl:choose>
        <xsl:when test="not($dmIdent)">
          <xsl:value-of select="string(//dmAddress/dmIdent/dmCode/@subSystemCode)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="string(descendant::dmCode/@subSystemCode)"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="subSubSystemCode">
      <xsl:choose>
        <xsl:when test="not($dmIdent)">
          <xsl:value-of select="string(//dmAddress/dmIdent/dmCode/@subSubSystemCode)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="string(descendant::dmCode/@subSubSystemCode)"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="assyCode">
      <xsl:choose>
        <xsl:when test="not($dmIdent)">
          <xsl:value-of select="string(//dmAddress/dmIdent/dmCode/@assyCode)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="string(descendant::dmCode/@assyCode)"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="disassyCode">
      <xsl:choose>
        <xsl:when test="not($dmIdent)">
          <xsl:value-of select="string(//dmAddress/dmIdent/dmCode/@disassyCode)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="string(descendant::dmCode/@disassyCode)"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="not($dmIdent)">
      </xsl:when>
      <xsl:otherwise>
      </xsl:otherwise>
    </xsl:choose>
    
    <xsl:variable name="combined">
      <xsl:value-of select="$systemCode"/>
      <xsl:text>.</xsl:text>
      <xsl:value-of select="number(concat($subSystemCode, $subSubSystemCode))"/>
      <xsl:text>.</xsl:text>
      <xsl:value-of select="$assyCode"/>
      <xsl:text>.</xsl:text>
      <xsl:value-of select="$disassyCode"/>
    </xsl:variable>

    <!-- <xsl:variable name="final_combined" select="php:function('preg_replace', '/0?(.0)+$/', '', $combined)"/> -->
    <xsl:variable name="final_combined" select="php:function('preg_replace', '/.?(00.?)+$/', '', $combined)"/>

    <xsl:if test="$final_combined">
      <xsl:text>Chapter </xsl:text>
      <xsl:value-of select="$final_combined"/>
    </xsl:if>
  </xsl:template>
  
  <!-- <xsl:template name="add_chapter">
    <xsl:variable name="systemCode">
      <xsl:value-of select="number(//dmAddress/dmIdent/dmCode/@systemCode)"/>
    </xsl:variable>
    <xsl:variable name="subSystemCode">
      <xsl:value-of select="number(//dmAddress/dmIdent/dmCode/@subSystemCode)"/>
    </xsl:variable>
    <xsl:variable name="subSubSystemCode">
      <xsl:value-of select="number(//dmAddress/dmIdent/dmCode/@subSubSystemCode)"/>
    </xsl:variable>
    <xsl:variable name="assyCode">
      <xsl:value-of select="number(//dmAddress/dmIdent/dmCode/@assyCode)"/>
    </xsl:variable>
    <xsl:variable name="disassyCode">
      <xsl:value-of select="number(//dmAddress/dmIdent/dmCode/@disassyCode)"/>
    </xsl:variable>

    <xsl:variable name="combined">
      <xsl:value-of select="$systemCode"/>
      <xsl:text>.</xsl:text>
      <xsl:value-of select="number(concat($subSystemCode, $subSubSystemCode))"/>
      <xsl:text>.</xsl:text>
      <xsl:value-of select="$assyCode"/>
      <xsl:text>.</xsl:text>
      <xsl:value-of select="$disassyCode"/>
    </xsl:variable>

    <xsl:variable name="final_combined" select="php:function('preg_replace', '/0?(.0)+$/', '', $combined)"/>

    <xsl:if test="$final_combined">
      <fo:block margin-bottom="6pt" >
        <xsl:text>Chapter </xsl:text>
        <xsl:value-of select="$final_combined"/>
      </fo:block>
    </xsl:if>
  </xsl:template> -->
</xsl:transform>