<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <!-- 
    Outstanding:
   -->

  <xsl:template name="highlights_and_updating">
    <xsl:param name="type">highlights and updating instruction</xsl:param>
    <xsl:apply-templates select="reducedPara"/>
    <xsl:for-each select="frontMatterSubList">
      <xsl:variable name="position" select="position()"/>
      <xsl:choose>
        <xsl:when test="$position = '1'">
          <fo:block-container margin-bottom="16pt">
            <fo:block font-size="12pt" text-align="center" font-weight="bold" margin-bottom="6pt" margin-top="6pt">
              <xsl:apply-templates select="title"/>    
            </fo:block>
            <xsl:apply-templates select="reducedPara"/>
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
          </fo:block-container>
        </xsl:when>
        <xsl:when test="$position = '2'">
          <fo:block-container>
            <fo:block font-size="12pt" text-align="center" font-weight="bold" margin-bottom="6pt" margin-top="6pt">
              <xsl:apply-templates select="title"/>    
            </fo:block>
            <xsl:apply-templates select="reducedPara"/>
            <fo:table width="100%" margin-top="14pt" border-top="1pt solid black" border-bottom="1pt solid black">
              <fo:table-column column-number="1" column-width="41%"/>
              <fo:table-column column-number="2" column-width="28%"/>
              <fo:table-column column-number="3" column-width="3%"/>
              <fo:table-column column-number="4" column-width="18%"/>
              <fo:table-column column-number="5" column-width="10%"/>
              <fo:table-header>
                <fo:table-row border-bottom="1pt solid black">
                  <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
                    <fo:block>Code</fo:block>
                  </fo:table-cell>
                  <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
                    <fo:block>Title</fo:block>
                  </fo:table-cell>
                  <fo:table-cell padding="4pt" padding-left="0pt" text-align="center">
                    <fo:block></fo:block>
                  </fo:table-cell>
                  <fo:table-cell padding="4pt" padding-left="0pt" text-align="center">
                    <fo:block>Date/No.</fo:block>
                  </fo:table-cell>
                  <fo:table-cell padding="4pt" padding-left="0pt" text-align="center">
                    <fo:block>Applic</fo:block>
                  </fo:table-cell>
                </fo:table-row>
              </fo:table-header>
              <fo:table-body>
                <xsl:for-each select="frontMatterPmEntry">
                  <xsl:call-template name="updating_entries"/>
                </xsl:for-each>
                <xsl:for-each select="frontMatterDmEntry">
                  <xsl:call-template name="updating_entries"/>
                </xsl:for-each>
                <xsl:for-each select="frontMatterExternalPubEntry">
                  <xsl:call-template name="updating_entries"/>
                </xsl:for-each>
              </fo:table-body>
            </fo:table>
          </fo:block-container>
        </xsl:when>
      </xsl:choose>
    </xsl:for-each>
    <xsl:if test="footnote">
      <fo:block>
        <xsl:apply-templates select="footnote">
          <xsl:with-param name="onlyBottom" select="1"/>
        </xsl:apply-templates>
      </fo:block>
    </xsl:if>
  </xsl:template>


</xsl:transform>