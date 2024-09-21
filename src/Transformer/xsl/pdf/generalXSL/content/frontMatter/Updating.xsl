<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <!-- 
    Outstanding:
   -->

  <xsl:template name="updating_entries">
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
        <fo:block font-size="10pt">
          <xsl:call-template name="get_code_frontMatterEntry">
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
        <fo:block font-size="10pt">
          <xsl:call-template name="get_title_frontMatterEntry"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding="4pt" padding-left="0pt" text-align="center">
        <fo:block font-family="calibri" font-size="10pt">
          <xsl:call-template name="issueTypeUPDT" />
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding="4pt" padding-left="0pt" text-align="center">
        <fo:block font-size="10pt">
          <xsl:call-template name="get_dateno_frontMatterEntry">
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding="4pt" padding-left="0pt" text-align="center">
        <fo:block font-size="10pt">
          <xsl:call-template name="get_applicability">
            <xsl:with-param name="applic" select="$entry//dmStatus/applic|$entry//pmStatus/applic"/>
          </xsl:call-template>
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


</xsl:transform>