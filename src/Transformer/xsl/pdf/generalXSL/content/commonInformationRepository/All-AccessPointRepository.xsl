<?xml version="1.0" encoding="UTF-8"?>

<!-- Outstanding
  1. belum mengakomodir ../accessPoint/accessPointRefGroup
  2. belum mengakomodir ../accessPointSpec/refs
  3. belum mengakomodir ../accessPoint/internalRef
  4. belum mengakomodir ../accessPoint/fastener
  5. belum mengakomodir ../accessPoint/accessPointRefGroup
-->

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template match="accessPointRepository">
    <fo:block>  
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_controlAuthority" />
      <xsl:call-template name="add_security"/>

      <fo:table width="100%" margin-top="14pt">
        <fo:table-column column-number="1" column-width="15%"/>
        <fo:table-column column-number="1" column-width="85%"/>
        <fo:table-header>
          <fo:table-row>
            <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
              <fo:block>No.</fo:block>
            </fo:table-cell>
            <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
              <fo:block>Detail</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="accessPointSpec|__cgmark[child::accessPointSpec]" />
        </fo:table-body>
      </fo:table>
    </fo:block>
  </xsl:template>

  <xsl:template match="accessPointSpec">
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
    <xsl:if test="descendant::accessPointIdent/@controlAuthorityRefs">
      <fo:table-row keep-together="always">
        <fo:table-cell number-columns-spanned="2">
          <xsl:call-template name="add_controlAuthority">
            <xsl:with-param name="id" select="descendant::accessPointIdent/@controlAuthorityRefs"/>
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
    <xsl:if test="descendant::accessPointIdent/@securityClassification or descendant::accessPointIdent/@commercialClassification or descendant::accessPointIdent/@caveat">
      <fo:table-row keep-together="always">
        <fo:table-cell number-columns-spanned="2">
          <xsl:call-template name="add_security">
            <xsl:with-param name="sc" select="descendant::accessPointIdent/@securityClassification"/>
            <xsl:with-param name="cc" select="descendant::accessPointIdent/@commercialClassification"/>
            <xsl:with-param name="caveat" select="descendant::accessPointIdent/@caveat"/> 
          </xsl:call-template>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>

    <fo:table-row>
      <xsl:call-template name="add_id"/>
      <fo:table-cell>
        <xsl:apply-templates select="accessPointIdent|__cgmark[child::accessPointIdent]"/>
      </fo:table-cell>
      <fo:table-cell>
        <xsl:apply-templates select="accessPointAlts|__cgmark[child::accessPointAlts]"/>
      </fo:table-cell>
    </fo:table-row>

    <xsl:if test="@controlAuthorityRefs or @securityClassification or @commercialClassification or @caveat or descendant::accessPointIdent/@controlAuthorityRefs or descendant::accessPointIdent/@securityClassification or descendant::accessPointIdent/@commercialClassification or descendant::accessPointIdent/@caveat">
      <fo:table-row keep-together="always">
        <fo:table-cell number-columns-spanned="2"><fo:block>&#160;</fo:block></fo:table-cell>
      </fo:table-row>
    </xsl:if>
  </xsl:template>

  <xsl:template match="accessPointIdent">
    <!-- tidak perlu call template add_authority or add_security karena sudah dipanggil di AccessPpointSpec
    jika dilakukan disini, space column tidak muat -->
    <fo:block>
      <xsl:call-template name="style-para"/>
      <xsl:call-template name="add_id"/>
      <xsl:value-of select="@accessPointNumber"/>
    </fo:block>
  </xsl:template>
  
  <xsl:template match="accessPointAlts">
    <fo:block>
      <xsl:call-template name="style-para"/>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <xsl:apply-templates select="accessPoint|__cgmark"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="accessPoint">
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_applicability"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>

      <xsl:apply-templates select="name|__cgmark[child::name]">
        <xsl:with-param name="select" select="descendant::name"/>
      </xsl:apply-templates>

      <xsl:if test="shortname and not(name)">
        <xsl:apply-templates select="shortName|__cgmark[child::shortName]">
          <xsl:with-param name="select" select="descendant::shortName"/>
        </xsl:apply-templates>
      </xsl:if>
      
      <xsl:apply-templates select="accessTo|__cgmark[child::accessTo]">
        <xsl:with-param name="select" select="descendant::accessTo"/>
      </xsl:apply-templates>

      <xsl:apply-templates select="quantity|__cgmark[child::quantity]">
        <xsl:with-param name="select" select="descendant::quantity"/>
      </xsl:apply-templates>

      <xsl:apply-templates select="hoursToOpen|__cgmark[child::hoursToOpen]">
        <xsl:with-param name="select" select="descendant::hoursToOpen"/>
      </xsl:apply-templates>

      <xsl:apply-templates select="hoursToClose|__cgmark[child::hoursToClose]">
        <xsl:with-param name="select" select="descendant::hoursToClose"/>
      </xsl:apply-templates> 
    </fo:block>
  </xsl:template>

  <xsl:template match="accessTo">
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_controlAuthority" />
      <xsl:call-template name="add_security"/>
      <xsl:text>Access to </xsl:text>
      <xsl:apply-templates/>
    </fo:block>
  </xsl:template>

  <xsl:template match="quantity[ancestor::accessPoint]">
    <fo:block>
      <xsl:call-template name="add_controlAuthority" />
      <xsl:call-template name="add_security"/>
      <xsl:text>Dimension area: </xsl:text>
      <xsl:apply-templates/>
    </fo:block>
  </xsl:template>

  <xsl:template match="hoursToOpen|hoursToClose">
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <xsl:apply-templates/>
    </fo:block>
  </xsl:template>
</xsl:transform>