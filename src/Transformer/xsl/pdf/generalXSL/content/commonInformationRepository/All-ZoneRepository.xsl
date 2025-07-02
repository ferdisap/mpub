<?xml version="1.0" encoding="UTF-8"?>

<!-- Outstanding
  1. belum mengakomodir element zone/shortname
  2. belum mengakomodir element zone/zoneSide
  3. belum mengakomodir element zone/boundaryFrom
  4. belum mengakomodir element zone/boundaryTo
  5. belum mengakomodir element zone/zoneRefGroup
  6. belum mengakomodir element zone/internalRef
  7. belum mengakomodir element zoneRefGroup
  8. belum mengakomodir element refs
-->

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template match="zoneRepository">
    <fo:block font-size="14pt" font-weight="bold" margin-bottom="6pt" margin-top="6pt"
      text-align="center">Common Information Repository - Zone Repository
    </fo:block>
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_controlAuthority" />
      <xsl:call-template name="add_security"/>

      <!-- major zone -->
      <fo:table width="100%">
        <fo:table-column column-number="1" column-width="15%" />
        <fo:table-column column-number="1" column-width="85%" />
        <fo:table-header>
          <fo:table-row>
            <fo:table-cell number-columns-spanned="2" text-align="center" padding-top="2pt">
              <fo:block font-weight="bold">Major Zone</fo:block>
            </fo:table-cell>
          </fo:table-row>
          <fo:table-row>
            <fo:table-cell padding="4pt" padding-left="0pt">
              <fo:block>Zone</fo:block>
            </fo:table-cell>
            <fo:table-cell padding="4pt" padding-left="0pt">
              <fo:block>Description</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="__cgmark[child::zoneSpec[@zoneType='majorzone']]|zoneSpec[@zoneType='majorzone']"/>
        </fo:table-body>
      </fo:table>

      <!-- subzone -->
      <fo:table width="100%" margin-top="14pt">
        <fo:table-column column-number="1" column-width="15%" />
        <fo:table-column column-number="1" column-width="85%" />
        <fo:table-header>
          <fo:table-row>
            <fo:table-cell number-columns-spanned="2" text-align="center" padding-top="2pt">
              <fo:block font-weight="bold">Sub Zone</fo:block>
            </fo:table-cell>
          </fo:table-row>
          <fo:table-row>
            <fo:table-cell padding="4pt" padding-left="0pt">
              <fo:block>Zone</fo:block>
            </fo:table-cell>
            <fo:table-cell padding="4pt" padding-left="0pt">
              <fo:block>Description</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="__cgmark[child::zoneSpec[@zoneType='subzone']]|zoneSpec[@zoneType='subzone']"/>
        </fo:table-body>
      </fo:table>

      <!-- spesific zone -->
      <fo:table width="100%" margin-top="14pt">
        <fo:table-column column-number="1" column-width="15%" />
        <fo:table-column column-number="1" column-width="85%" />
        <fo:table-header>
          <fo:table-row>
            <fo:table-cell number-columns-spanned="2" text-align="center" padding-top="2pt">
              <fo:block font-weight="bold">Specific Zone</fo:block>
            </fo:table-cell>
          </fo:table-row>
          <fo:table-row>
            <fo:table-cell padding="4pt" padding-left="0pt">
              <fo:block>Zone</fo:block>
            </fo:table-cell>
            <fo:table-cell padding="4pt" padding-left="0pt">
              <fo:block>Description</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="__cgmark[child::zoneSpec[@zoneType='specificZone']]|zoneSpec[@zoneType='specificZone']"/>
        </fo:table-body>
      </fo:table>
    </fo:block>
  </xsl:template>

  <xsl:template match="zoneSpec">
    <!-- Penambahan Auth refs dan security -->
    <xsl:if test="@controlAuthorityRefs">
      <fo:table-row keep-together="always">
        <fo:table-cell number-columns-spanned="2">
          <fo:block>
            <xsl:call-template name="add_controlAuthority" />
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
    <xsl:if test="descendant::zoneIdent/@controlAuthorityRefs">
      <fo:table-row keep-together="always">
        <fo:table-cell number-columns-spanned="2">
          <xsl:call-template name="add_controlAuthority">
            <xsl:with-param name="id" select="descendant::zoneIdent/@controlAuthorityRefs"/>
          </xsl:call-template>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
    <xsl:if test="@securityClassification or @commercialClassification or @caveat">
      <fo:table-row keep-together="always">
        <fo:table-cell number-columns-spanned="2">
          <xsl:call-template name="add_security"/>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
    <xsl:if test="descendant::zoneIdent/@securityClassification or descendant::zoneIdent/@commercialClassification or descendant::zoneIdent/@caveat">
      <fo:table-row keep-together="always">
        <fo:table-cell number-columns-spanned="2">
          <xsl:call-template name="add_security">
            <xsl:with-param name="sc" select="descendant::zoneIdent/@securityClassification"/>
            <xsl:with-param name="cc" select="descendant::zoneIdent/@commercialClassification"/>
            <xsl:with-param name="caveat" select="descendant::zoneIdent/@caveat"/> 
          </xsl:call-template>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>

    <fo:table-row>
      <xsl:call-template name="add_id"/>
      <fo:table-cell>
        <xsl:apply-templates select="zoneIdent|__cgmark[child::zoneIdent]"/>
      </fo:table-cell>
      <fo:table-cell>
        <xsl:apply-templates select="zoneAlts|__cgmark[child::zoneAlts]"/>
      </fo:table-cell>
    </fo:table-row>

    <!-- space jika ada penambahan control Auth refs dan security -->
    <xsl:if test="@controlAuthorityRefs or @securityClassification or @commercialClassification or @caveat or descendant::zoneIdent/@controlAuthorityRefs or descendant::zoneIdent/@securityClassification or descendant::zoneIdent/@commercialClassification or descendant::zoneIdent/@caveat">
      <fo:table-row keep-together="always">
        <fo:table-cell number-columns-spanned="2"><fo:block>&#160;</fo:block></fo:table-cell>
      </fo:table-row>
    </xsl:if>
  </xsl:template>

  <xsl:template match="zoneIdent">
    <!-- tidak perlu call template add_authority or add_security karena sudah dipanggil di zoneSpec
    jika dilakukan disini, space column tidak muat -->
    <fo:block>
      <xsl:call-template name="style-para"/>
      <xsl:call-template name="add_id"/>
      <xsl:value-of select="@zoneNumber"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="zoneAlts">
    <fo:block>
      <xsl:call-template name="style-para"/>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <xsl:apply-templates select="zone|__cgmark"/>
    </fo:block>
  </xsl:template>
  
  <xsl:template match="zone">
    <fo:block text-align="left">
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_applicability"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <xsl:apply-templates select="itemDescr|__cgmark[child::itemDescr]"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="itemDescr">
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <xsl:value-of select="."/>
    </fo:block>
  </xsl:template>


</xsl:transform>