<?xml version="1.0" encoding="UTF-8"?>

<!-- 
  Outstanding:
  - <dataRestriction> belum dibuat
  - @frontMatterInfoType (fmi-xx) belum dibuat
-->

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template name="poh-frontMatterTitlePage">
    <xsl:call-template name="poh-productIntroName"/>
    <xsl:call-template name="poh-productAndModel"/>
    <xsl:call-template name="poh-pmTitle"/>
    <xsl:call-template name="poh-shortPmTitle"/>
    
    <fo:block>
      <xsl:call-template name="poh-pmCode"/>
      <fo:inline-container inline-progression-dimension="30%">
        <xsl:call-template name="poh-issueInfo"/>
      </fo:inline-container>
      <fo:inline-container>
        <xsl:call-template name="poh-issueDate"/>
      </fo:inline-container>
    </fo:block>

    <xsl:call-template name="poh-productIllustration"/>
    <xsl:call-template name="copyright"/>

    <!-- externalPubCode -->
    <xsl:if test="externalPubCode">
      <fo:block-container font-size="8pt" margin-top="3pt">
        <fo:block>External Publication related:</fo:block>
        <xsl:for-each select="externalPubCode">
          <fo:block>
            <xsl:value-of select="@pubCodingScheme"/><xsl:text>:</xsl:text><xsl:apply-templates/> <xsl:text>   </xsl:text>
          </fo:block>
        </xsl:for-each>
      </fo:block-container>
    </xsl:if>

    <!-- write dervative classification here -->

    <xsl:choose>
      <xsl:when test="
      string(enterpriseSpec/enterpriseName) = string(responsiblePartnerCompany/enterpriseName)
      or
      string(enterpriseLogo/@infoEntityIdent) = string(publisherLogo/@infoEntityIdent)
      ">
        <xsl:call-template name="poh-manufacturer"/>
      </xsl:when>
      <xsl:otherwise>
        <!-- manufacturer -->
        <xsl:call-template name="poh-manufacturer"/>
        <!-- publisher -->
        <xsl:call-template name="poh-publisher"/>
      </xsl:otherwise>
    </xsl:choose>    

    <xsl:apply-templates select="security"/>
    <xsl:apply-templates select="barCode"/>
    
    <fo:block-container break-before="page" font-size="8pt">
      <fo:block margin-top="6pt">
        <fo:block margin-top="3pt"><xsl:apply-templates select="dataRestrictions/restrictionInfo/policyStatement"/></fo:block>
        <fo:block margin-top="3pt"><xsl:apply-templates select="dataRestrictions/restrictionInfo/dataConds"/></fo:block>
      </fo:block>
      <fo:block margin-top="6pt">
        <fo:block margin-top="3pt"><xsl:apply-templates select="dataRestrictions/restrictionInstructions/dataDistribution"/></fo:block>
        <fo:block margin-top="3pt"><xsl:apply-templates select="dataRestrictions/restrictionInstructions/exportControl"/></fo:block>
        <fo:block margin-top="3pt"><xsl:apply-templates select="dataRestrictions/restrictionInstructions/dataHandling"/></fo:block>
        <fo:block margin-top="3pt"><xsl:apply-templates select="dataRestrictions/restrictionInstructions/dataDestruction"/></fo:block>
        <fo:block margin-top="3pt"><xsl:apply-templates select="dataRestrictions/restrictionInstructions/dataDisclosure"/></fo:block>
      </fo:block>
      <fo:block margin-top="3pt">
        <xsl:apply-templates select="frontMatterInfo"/>
      </fo:block>
    </fo:block-container>
  </xsl:template>

  <xsl:template name="poh-productIntroName">
    <fo:block xsl:use-attribute-sets="poh-fmIntroName">
      <xsl:apply-templates select="productIntroName/name" />
    </fo:block>
  </xsl:template>

  <xsl:template name="poh-productAndModel">
    <xsl:if test="productAndModel/productName">
      <fo:block font-size="8pt"><xsl:apply-templates select="productAndModel/productName"/></fo:block>
    </xsl:if>
    <xsl:for-each select="productAndModel/productModel">
      <fo:block-container font-size="8pt">
        <fo:block>
          <fo:inline>Model: <xsl:apply-templates select="modelName"/></fo:inline>
          <xsl:if test="natoStockNumber">
            <xsl:text>   </xsl:text>
            <fo:inline>NSN: <xsl:apply-templates select="natoStockNumber"/></fo:inline>
          </xsl:if>
          <xsl:if test="identNumber">
            <xsl:text>   </xsl:text>
            <fo:inline>Manufacture Code: <xsl:apply-templates select="identNumber/manufacturerCode"/></fo:inline>
          </xsl:if>
        </fo:block>
      </fo:block-container>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="poh-pmTitle">
    <fo:block xsl:use-attribute-sets="poh-fmPmTitle">
      <xsl:apply-templates select="pmTitle/."/>
    </fo:block>
  </xsl:template>

  <xsl:template name="poh-shortPmTitle">
    <fo:block xsl:use-attribute-sets="poh-fmShortPmTitle">
      <xsl:apply-templates select="shortPmTitle/."/>
    </fo:block>
  </xsl:template>
  
  <xsl:template name="poh-pmCode">
    <fo:block xsl:use-attribute-sets="poh-fmPmCode">
      <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmCode', pmCode/.)" />
    </fo:block>
  </xsl:template>

  <xsl:template name="poh-issueInfo">
    <fo:block xsl:use-attribute-sets="poh-fmPmIssueInfo">
      Issue No.: <xsl:value-of select="issueInfo/@issueNumber"/>
    </fo:block>
  </xsl:template>
  
  <xsl:template name="poh-issueDate">
    <fo:block xsl:use-attribute-sets="poh-fmPmIssueDate">
      Issue Date: <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', issueDate/.)"/>
    </fo:block>
  </xsl:template>

  <xsl:template name="poh-productIllustration">
    <xsl:for-each select="productIllustration/graphic">
      <fo:block text-align="center">
        <fo:external-graphic src="url('{unparsed-entity-uri(@infoEntityIdent)}')" content-width="scale-to-fit" width="50%"></fo:external-graphic>
      </fo:block>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="poh-manufacturer">
    <fo:block-container margin-top="11pt">
        <fo:block font-size="8pt">Manufacturer:</fo:block>
        <fo:block font-size="8pt">
          <fo:table table-layout="fixed" width="100%">
            <fo:table-body>
              <fo:table-row>
                <fo:table-cell width="2cm">
                  <xsl:apply-templates select="enterpriseLogo"/>
                </fo:table-cell>
                <fo:table-cell display-align="top">
                  <xsl:apply-templates select="enterpriseSpec"/>
                </fo:table-cell>
              </fo:table-row>
            </fo:table-body>
          </fo:table>
        </fo:block>
      </fo:block-container>
  </xsl:template>

  <xsl:template name="poh-publisher">
    <fo:block-container margin-top="11pt">
        <fo:block font-size="8pt">Publisher:</fo:block>
        <fo:block font-size="8pt">
          <fo:table table-layout="fixed" width="100%">
            <fo:table-body>
              <fo:table-row>
                <fo:table-cell width="2cm">
                  <xsl:apply-templates select="publisherLogo"/>
                  <fo:block></fo:block>
                </fo:table-cell>
                <fo:table-cell display-align="top">
                  <fo:block>
                    <xsl:call-template name="add_id">
                      <xsl:with-param name="id" select="responsiblePartnerCompany/@id"/>
                    </xsl:call-template>
                    <xsl:value-of select="string(responsiblePartnerCompany/enterpriseName)"/>
                  </fo:block>
                </fo:table-cell>
              </fo:table-row>
            </fo:table-body>
          </fo:table>
        </fo:block>
      </fo:block-container>
  </xsl:template>
</xsl:transform>