<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

    <xsl:template match="commonInfo">
      <fo:block>
        <xsl:call-template name="cgmark_begin"/>
        <xsl:call-template name="add_id"/>
        <xsl:call-template name="add_controlAuthority"/>
        <xsl:apply-templates/>
        <xsl:call-template name="cgmark_end"/>
      </fo:block>

    </xsl:template>

    <xsl:template match="commonInfoDescrPara">
      <fo:block>
        <xsl:call-template name="add_id"/>
        <xsl:call-template name="cgmark_begin"/>
        <xsl:call-template name="add_applicability"/>
        <xsl:call-template name="add_security"/>
        <xsl:call-template name="add_controlAuthority"/>
        <xsl:call-template name="add_warning"/>
        <xsl:call-template name="add_caution"/>
        <xsl:call-template name="cgmark_end"/>
        <xsl:apply-templates/>        
      </fo:block>
    </xsl:template>

</xsl:transform>