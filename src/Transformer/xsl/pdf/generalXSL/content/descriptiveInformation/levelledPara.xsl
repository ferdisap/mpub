<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">
  
  <xsl:template match="levelledPara">
    <xsl:param name="level">
      <xsl:text>s</xsl:text>
      <xsl:variable name="lvl">
        <xsl:number level="multiple" count="levelledPara"/>
      </xsl:variable>
      <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::checkLevelByPrefix', $lvl)"/>
    </xsl:param>

    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_applicability"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>
    
    <fo:block text-align="justify" start-indent="0cm">
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="style-levelledPara">
        <xsl:with-param name="level" select="$level"/>
      </xsl:call-template>

      <xsl:call-template name="add_warning"/>
      <xsl:call-template name="add_caution"/>

      <xsl:apply-templates>
        <xsl:with-param name="level" select="$level"/>
      </xsl:apply-templates>
    </fo:block>
  </xsl:template>

  </xsl:transform>