<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <!-- 
    Outstanding:
    1. Sementara untuk reasonForUpdate ditulis secara manual di elemen reasonForUpdate. 
       Tapi nanti bisa diubah secara otomatis, dengan opendocument dan mengambil reasonForUpdate terakhir di dm/pmStatus 
       Sepertinya tidak boleh otomatis, karena rfu di dm/pmStatus itu bisa lebih dari satu, agar bisa direferensikan oleh internal elemennya

   -->

  <xsl:template name="highlights">
    <xsl:param name="type">highlights</xsl:param>
    <xsl:apply-templates select="reducedPara"/>
    <xsl:for-each select="frontMatterSubList">
      <xsl:apply-templates select="title"/>
      <fo:table width="100%" margin-top="14pt" border-top="1pt solid black" border-bottom="1pt solid black">
        <fo:table-column column-number="1" column-width="50%"/>
        <fo:table-column column-number="2" column-width="50%"/>
        <fo:table-header>
          <fo:table-row border-bottom="1pt solid black">
            <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
              <fo:block>Code</fo:block>
            </fo:table-cell>
            <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
              <fo:block>Reason for update</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:for-each select="frontMatterPmEntry">
            <xsl:call-template name="highlights_entries"/>
          </xsl:for-each>
          <xsl:for-each select="frontMatterDmEntry">
            <xsl:call-template name="highlights_entries"/>
          </xsl:for-each>
          <xsl:for-each select="frontMatterExternalPubEntry">
            <xsl:call-template name="highlights_entries"/>
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

  <xsl:template name="highlights_entries">
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
      <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
        <fo:block>
          <xsl:apply-templates select="reasonForUpdate"/>
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