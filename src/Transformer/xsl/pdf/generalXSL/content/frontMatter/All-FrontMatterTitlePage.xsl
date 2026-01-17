<?xml version="1.0" encoding="UTF-8"?>

<!-- 
  Outstanding:
  - <dataRestriction> belum dibuat
  - @frontMatterInfoType (fmi-xx) belum dibuat
-->

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <!-- <xsl:template match="content[name(child::*) = 'frontMatter']">
    <xsl:variable name="dmIdent" select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmIdent', //identAndStatusSection/dmAddress/dmIdent, '', '')"/>
    <fo:block-container id="{$dmIdent}">
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:apply-templates/>
    </fo:block-container>
  </xsl:template> -->

  <!-- <xsl:template match="frontMatterTitlePage">
    <fo:block>Lorem ipsum dolor sit amet consectetur adipisicing elit. Animi sapiente eos reprehenderit iure veritatis, quam, soluta nulla cupiditate eaque fugit recusandae repellat distinctio porro eum obcaecati optio officia? Explicabo, voluptates! Rem doloribus repudiandae voluptas velit necessitatibus illo quaerat mollitia error iusto perspiciatis, laudantium odio, exercitationem obcaecati provident? Dignissimos ut ad ipsam, obcaecati rerum sed itaque animi? Accusantium debitis voluptatibus vero quisquam, cum corrupti dolorum earum perspiciatis nulla rem aperiam praesentium vitae modi enim, unde quidem quis impedit veniam deleniti eum cumque saepe corporis? Unde corporis fugiat explicabo eos dicta doloribus id et. Iste quae minus blanditiis, deserunt, nihil, asperiores assumenda cumque tenetur quam praesentium id consequuntur vitae ad rerum cupiditate corrupti animi totam molestiae. Alias quo fuga cum accusantium tempore dolorem mollitia eum consequuntur odio veniam? Ipsa, doloribus rerum, enim suscipit quisquam ullam impedit dicta nostrum debitis similique quibusdam, corporis dolor. Ratione ex molestiae assumenda ipsum quasi dolore dolorum temporibus! Odio velit quasi assumenda necessitatibus repellat, placeat nihil fuga ipsum non, quo nesciunt repellendus sapiente, quis iste! Ab quaerat, reprehenderit amet odio, facilis architecto libero aspernatur et cum minus esse sint quibusdam? Molestias blanditiis impedit vero, accusantium saepe, assumenda, culpa ipsam dolore incidunt explicabo officiis voluptatibus voluptas. Maxime, porro eum?</fo:block>
  </xsl:template> -->
  <xsl:template match="frontMatterTitlePage">
    <xsl:param name="masterName"><xsl:call-template name="get_PDF_MasterName"/></xsl:param>    
    <xsl:choose>
      <xsl:when test="$masterName = 'poh'">
        <xsl:call-template name="poh-frontMatterTitlePage"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="default-frontMatterTitlePage"/>
      </xsl:otherwise>
    </xsl:choose>
    <!-- <xsl:call-template name="default-frontMatterTitlePage"/> -->
  </xsl:template>

  <xsl:template name="default-frontMatterTitlePage">
    <xsl:call-template name="default-productIntroName"/>
    <xsl:call-template name="default-productAndModel"/>
    <xsl:call-template name="default-pmTitle"/>
    <xsl:call-template name="default-shortPmTitle"/>
    
    <fo:block>
      <xsl:call-template name="default-pmCode"/>
      <fo:inline-container inline-progression-dimension="30%">
        <xsl:call-template name="default-issueInfo"/>
      </fo:inline-container>
      <fo:inline-container>
        <xsl:call-template name="default-issueDate"/>
      </fo:inline-container>
    </fo:block>

    <xsl:call-template name="default-productIllustration"/>
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

    <!-- dervative classification here -->

    <!-- manufacturer -->
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

    <!-- publisher -->
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

  <xsl:template name="default-productIntroName">
    <fo:block xsl:use-attribute-sets="fmIntroName">
      <xsl:apply-templates select="productIntroName/name" />
    </fo:block>
  </xsl:template>

  <xsl:template name="default-productAndModel">
    <xsl:if test="productAndModel/productName">
      <fo:block class="productName"><xsl:apply-templates select="productAndModel/productName"/></fo:block>
    </xsl:if>
    <xsl:for-each select="productAndModel/productModel">
      <fo:block-container>
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

  <xsl:template name="default-pmTitle">
    <fo:block xsl:use-attribute-sets="fmPmTitle">
      <xsl:apply-templates select="pmTitle/."/>
    </fo:block>
  </xsl:template>

  <xsl:template name="default-shortPmTitle">
    <fo:block xsl:use-attribute-sets="fmShortPmTitle">
      <xsl:apply-templates select="shortPmTitle/."/>
    </fo:block>
  </xsl:template>

  <xsl:template name="default-pmCode">
    <fo:block xsl:use-attribute-sets="fmPmCode">
      <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmCode', pmCode/.)" />
    </fo:block>
  </xsl:template>

  <xsl:template name="default-issueInfo">
    <fo:block xsl:use-attribute-sets="fmPmIssueInfo">
      Issue No.: <xsl:value-of select="issueInfo/@issueNumber"/>
    </fo:block>
  </xsl:template>
  
  <xsl:template name="default-issueDate">
    <fo:block xsl:use-attribute-sets="fmPmIssueDate">
      Issue Date: <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', issueDate/.)"/>
    </fo:block>
  </xsl:template>

  <xsl:template name="default-productIllustration">
    <xsl:for-each select="productIllustration/graphic">
      <fo:block text-align="center">
        <fo:external-graphic src="url('{unparsed-entity-uri(@infoEntityIdent)}')" content-width="scale-to-fit">
          <xsl:call-template name="setGraphicDimension"/>
        </fo:external-graphic>
      </fo:block>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="enterpriseLogo">
    <fo:block>
      <xsl:for-each select="symbol">
        <fo:external-graphic src="url('{unparsed-entity-uri(@infoEntityIdent)}')" content-width="scale-to-fit" width="1.5cm"/>
      </xsl:for-each>
    </fo:block>
  </xsl:template>
  
  <xsl:template match="publisherLogo">
    <fo:block>
      <xsl:for-each select="symbol">
        <fo:external-graphic src="url('{unparsed-entity-uri(@infoEntityIdent)}')" content-width="scale-to-fit" width="1.5cm"/>
      </xsl:for-each>
    </fo:block>
  </xsl:template>

  <xsl:template match="frontMatterInfo">
    <fo:block-container margin-top="6pt">
      <fo:block xsl:use-attribute-sets="h1">
        <xsl:value-of select="title"/>
      </fo:block>
      <xsl:apply-templates select="*[name() != 'title']"/>
    </fo:block-container>
  </xsl:template>

  <xsl:template name="copyright">
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>
    <fo:block-container font-size="8pt" margin-top="8pt">
      <xsl:apply-templates select="dataRestrictions/restrictionInfo/copyright/copyrightPara"/>
    </fo:block-container>
  </xsl:template>

  <xsl:template match="copyrightPara">
    <xsl:call-template name="add_applicability"/>
    <fo:block margin-top="0pt">
      <xsl:apply-templates>
        <xsl:with-param name="listElemMarginTop">0pt</xsl:with-param>
      </xsl:apply-templates>
    </fo:block>
  </xsl:template>
  

  
</xsl:transform>