<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

    <xsl:include href="./All-CircuitBreakerRepository.xsl" />
    <xsl:include href="./All-ZoneRepository.xsl" />
    <xsl:include href="./All-AccessPointRepository.xsl" />

    <xsl:template match="commonRepository">
      <fo:block text-align="justify" start-indent="0">
        <xsl:call-template name="add_id"/>
        <xsl:call-template name="add_controlAuthority"/>
        <xsl:call-template name="add_security"/>
        <xsl:apply-templates select="commonInfo"/>
        <xsl:apply-templates select="__cgmark|functionalItemRepository|circuitBreakerRepository|partRepository|zoneRepository|accessPointRepository|toolRepository|enterpriseRepository|supplyRepository|supplyRqmtRepository|functionalPhysicalAreaRepository|controlIndicatorRepository|applicRepository|warningRepository|cautionRepository"/>
        <xsl:apply-templates select="figure|figureAlts|multimedia|multimediaAlts"/>
      </fo:block>
    </xsl:template>

</xsl:transform>