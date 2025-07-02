<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:fox="http://xmlgraphics.apache.org/fop/extensions"
  xmlns:php="http://php.net/xsl">

  <xsl:template match="security">
    <xsl:apply-templates select="@securityClassification"/>
  </xsl:template>

  <xsl:template match="@securityClassification[parent::security]">
    <xsl:param name="useInterpreter" select="'yes'"/>
    <xsl:variable name="number">
      <xsl:value-of select="."/>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="$useInterpreter = 'yes'">
        <xsl:call-template name="interpretSC">
          <xsl:with-param name="scCode" select="$number"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$number"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="add_security">
    <xsl:variable name="sc" select="@securityClassification"/>
    <xsl:variable name="cc" select="@commercialClassification"/>
    <xsl:variable name="caveat" select="@caveat"/>

    <xsl:if test="$sc or $cc or $caveat or @derivativeClassification">
      <fo:block font-size="8pt">
        <xsl:if test="@securityClassification">
          <xsl:call-template name="interpretSC"/>
          <xsl:text>. </xsl:text>
        </xsl:if>
        <xsl:if test="@commercialClassification">
          <xsl:call-template name="interpretCC"/>
          <xsl:text>. </xsl:text>
        </xsl:if>
        <xsl:if test="@caveat">
          <xsl:call-template name="interpretCaveat"/>
          <xsl:text>. </xsl:text>
        </xsl:if>
        <xsl:call-template name="add_derivativeClassification"/>
      </fo:block>
    </xsl:if>
  </xsl:template>

  <xsl:template name="add_inline_security">
    <xsl:variable name="sc" select="@securityClassification"/>
    <xsl:variable name="cc" select="@commercialClassification"/>
    <xsl:variable name="caveat" select="@caveat"/>

    <fo:inline font-size="8pt">
      <xsl:if test="@securityClassification">
        <xsl:call-template name="interpretSC"/>
        <xsl:text>. </xsl:text>
      </xsl:if>
      <xsl:if test="@commercialClassification">
        <xsl:call-template name="interpretCC"/>
        <xsl:text>. </xsl:text>
      </xsl:if>
      <xsl:if test="@caveat">
        <xsl:call-template name="interpretCaveat"/>
        <xsl:text>. </xsl:text>
      </xsl:if>
      <xsl:call-template name="add_derivativeClassification"/>
    </fo:inline>
  </xsl:template>

  <xsl:template name="add_derivativeClassification">
    <xsl:param name="dc" select="@derivativeClassificationRefId"/>
    <xsl:if test="$dc">
      <xsl:for-each select="//classificationActionGroup[@id = $dc]/classificationAction">
        <xsl:variable name="dateAction" select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', .)"/>
        <xsl:variable name="action"><xsl:call-template name="interpretDCActionType"/></xsl:variable>
        <fo:block font-size="8pt">
          <xsl:text>Source from </xsl:text><xsl:value-of select="derivativeSource"/><xsl:text>. </xsl:text>
          <xsl:value-of select="$dateAction"/><xsl:text>, </xsl:text><xsl:value-of select="$action"/><xsl:text>. </xsl:text>
          <xsl:value-of select="string(businessUnit/businessUnitName)"/>
          <xsl:apply-templates select="businessUnit/businessUnitAddress">
            <xsl:with-param name="fontSize">8pt</xsl:with-param>
          </xsl:apply-templates>
          <xsl:apply-templates select="businessUnit/contactPerson">
            <xsl:with-param name="fontSize">8pt</xsl:with-param>
          </xsl:apply-templates>
        </fo:block>
      </xsl:for-each>
    </xsl:if>
  </xsl:template>

  <xsl:template name="interpretSC">
    <xsl:param name="scCode" select="@securityClassification"/>
    <xsl:if test="$scCode">
      <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','securityClassification', string($scCode), 'extension', 'default')" />
    </xsl:if>
  </xsl:template>

  <xsl:template name="interpretCC">
    <xsl:param name="ccCode" select="@commercialClassification"/>
    <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','commercialClassification', string($ccCode), 'extension', 'default')" />
  </xsl:template>

  <xsl:template name="interpretCaveat">
    <xsl:param name="caveat" select="@caveat"/>
    <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','caveat', string($caveat), 'extension', 'default')" />
  </xsl:template>
  
  <xsl:template name="interpretDCActionType">
    <xsl:param name="identType" select="@actionIdentType"/>
    <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','derivativeClassification', string($identType), 'extension', 'default')" />
  </xsl:template>

</xsl:transform>