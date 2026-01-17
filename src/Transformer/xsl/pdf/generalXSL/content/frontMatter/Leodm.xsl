<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <!-- digunakan di element frontMatterList -->
  <xsl:template name="leodm">
    <xsl:param name="type">leodm</xsl:param>
    <xsl:apply-templates select="reducedPara"/>
    <xsl:for-each select="frontMatterSubList">
      <xsl:apply-templates select="title"/>
      <fo:table width="100%" margin-top="14pt" border-top="1pt solid black" border-bottom="1pt solid black">
        <fo:table-column column-number="1" column-width="50%"/>
        <fo:table-column column-number="2" column-width="3%"/>
        <fo:table-column column-number="3" column-width="22%"/>
        <fo:table-column column-number="4" column-width="15%"/>
        <fo:table-column column-number="5" column-width="10%"/>
        <fo:table-header>
          <fo:table-row border-bottom="1pt solid black">
            <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
              <fo:block>Code</fo:block>
            </fo:table-cell>
            <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
              <fo:block></fo:block>
            </fo:table-cell>
            <fo:table-cell padding="4pt" padding-left="0pt" text-align="center">
              <fo:block>Date/No.</fo:block>
            </fo:table-cell>
            <fo:table-cell padding="4pt" padding-left="0pt" text-align="center">
              <fo:block>Applic</fo:block>
            </fo:table-cell>
            <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
              <fo:block>Remarks</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:for-each select="frontMatterPmEntry">
            <xsl:call-template name="leodm_entries"/>
          </xsl:for-each>
          <xsl:for-each select="frontMatterDmEntry">
            <xsl:call-template name="leodm_entries"/>
          </xsl:for-each>
          <xsl:for-each select="frontMatterExternalPubEntry">
            <xsl:call-template name="leodm_entries"/>
          </xsl:for-each>
        </fo:table-body>
      </fo:table>
    </xsl:for-each>
    <xsl:if test="footnote">
      <fo:block>
        <xsl:apply-templates select="footnote">
          <xsl:with-param name="onlyBottom" select="1"/>
        </xsl:apply-templates>
      </fo:block>
    </xsl:if>
  </xsl:template>

  <xsl:template name="leodm_entries">
    <xsl:if test="@applicRefId or @controlAuthorityRefs">
      <fo:table-row>
        <fo:table-cell number-columns-spanned="5" padding="4pt" padding-left="0pt" text-align="left">
          <xsl:call-template name="add_applicability"/>
          <xsl:call-template name="add_controlAuthority"/>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
    <xsl:variable name="entry" select="php:function(
      'Ptdi\Mpub\Main\CSDBStatic::document',
      $csdb_path,
      php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_ident', descendant::dmRefIdent|descendant::pmRefIdent)
      )"/>
    <fo:table-row>
      <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
        <fo:block>
          <xsl:call-template name="get_code_frontMatterEntry">
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding="4pt" padding-left="0pt" text-align="center">
        <fo:block font-family="calibri">
          <xsl:call-template name="issueTypeLEODM" />
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding="4pt" padding-left="0pt" text-align="center">
        <fo:block>
          <xsl:call-template name="get_dateno_frontMatterEntry">
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding="4pt" padding-left="0pt" text-align="center">
        <fo:block>
          <xsl:call-template name="get_applicability">
            <xsl:with-param name="applic" select="$entry//dmStatus/applic|$entry//pmStatus/applic"/>
          </xsl:call-template>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
        <fo:block>
          <xsl:apply-templates select="footnoteRemarks" />
        </fo:block>
      </fo:table-cell>
    </fo:table-row>
    <xsl:if test="@applicRefId or @controlAuthorityRefs">
      <fo:table-row>
        <fo:table-cell number-columns-spanned="5" padding="4pt" padding-left="0pt" text-align="left">
          <fo:block></fo:block>
        </fo:table-cell>
      </fo:table-row>
    </xsl:if>
  </xsl:template>
  
  <xsl:template name="issueTypeLEODM">
    <xsl:variable name="issueType"><xsl:value-of select="@issueType" /></xsl:variable>
    <xsl:choose>
      <xsl:when test="$issueType = 'new'">N</xsl:when>
      <xsl:when test="$issueType = 'revised'">C</xsl:when>
      <xsl:when test="$issueType = 'changed'">C</xsl:when>
      <xsl:when test="$issueType = 'status'">C</xsl:when>
      <xsl:when test="$issueType = 'reinstate-status'">N</xsl:when>
      <xsl:when test="$issueType = 'reinstate-changed'">N</xsl:when>
      <xsl:when test="$issueType = 'reinstate-revised'">N</xsl:when>
      <xsl:otherwise><xsl:value-of select="$issueType" /></xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:transform>