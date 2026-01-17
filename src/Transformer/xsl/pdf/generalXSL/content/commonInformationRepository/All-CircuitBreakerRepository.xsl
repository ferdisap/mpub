<?xml version="1.0" encoding="UTF-8"?>

<!-- Outstanding
  1. belum mengakomodir elemen circuitBreakerIdent/@itemOriginator
  2. belum mengakomodir elemen circuitBreakerIdent/@contextIdent
  3. belum mengakomodir elemen circuitBreakerIdent/@manufacturerCodeValue
  4. belum mengakomodir elemen functionalItemRefGroup dan attribute nya
  5. belum mengakomodir elemen circuitBreakerRefGroup dan attribute nya
  6. belum mengakomodir elemen functionalPhysicalAreaRef dan attribute nya
  7. belum mengakomodir elemen refs
-->

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

    <xsl:template match="circuitBreakerRepository">
      <fo:block>
        <xsl:call-template name="add_id"/>
        <xsl:call-template name="add_controlAuthority"/>
        <xsl:call-template name="add_security"/>

        <fo:table border-top="1pt solid black" border-bottom="1pt solid black" width="100%">
          <fo:table-column column-number="1" column-width="20%"/>
          <fo:table-column column-number="1" column-width="35%"/>
          <fo:table-column column-number="1" column-width="10%"/>
          <fo:table-column column-number="1" column-width="15%"/>
          <fo:table-column column-number="1" column-width="8%"/>
          <fo:table-column column-number="1" column-width="12%"/>
          <fo:table-header>
            <fo:table-row border-bottom="1pt solid black">
              <fo:table-cell padding="4pt"><fo:block>Label</fo:block></fo:table-cell>
              <fo:table-cell padding="4pt"><fo:block>Name/function</fo:block></fo:table-cell>
              <fo:table-cell padding="4pt" text-align="center"><fo:block>FIN</fo:block></fo:table-cell>
              <fo:table-cell padding="4pt" text-align="center"><fo:block>Group</fo:block></fo:table-cell>
              <fo:table-cell padding="4pt" text-align="center"><fo:block>Amp</fo:block></fo:table-cell>
              <fo:table-cell padding="4pt" text-align="center"><fo:block>Panel</fo:block></fo:table-cell>
            </fo:table-row>
          </fo:table-header>
          <fo:table-body>
            <fo:table-row keep-together="always">
              <fo:table-cell number-columns-spanned="6">
                <!-- ini untuk space after table header to table body -->
                <fo:block font-size="6pt">&#160;</fo:block>
              </fo:table-cell>
            </fo:table-row>
            <xsl:for-each select="circuitBreakerSpec|__cgmark/circuitBreakerSpec">
              <xsl:if test="parent::__cgmark or __cgmark[child::circuitBreakerAlts]">
                <fo:table-row keep-together="always">
                  <fo:table-cell number-columns-spanned="6">
                    <fo:change-bar-begin change-bar-class="{generate-id(.)}" change-bar-style="solid" change-bar-width="0.5pt" change-bar-offset="0.5cm"/>
                  </fo:table-cell>
                </fo:table-row>
              </xsl:if>
              <xsl:if test="@controlAuthorityRefs">
                <fo:table-row keep-together="always">
                  <fo:table-cell number-columns-spanned="6">
                    <xsl:call-template name="add_controlAuthority" />
                  </fo:table-cell>
                </fo:table-row>
              </xsl:if>
              <xsl:if test="circuitBreakerAlts/@controlAuthorityRefs">
                <fo:table-row keep-together="always">
                  <fo:table-cell number-columns-spanned="6">
                    <xsl:call-template name="add_controlAuthority">
                      <xsl:with-param name="id" select="circuitBreakerAlts/@controlAuthorityRefs"/>
                    </xsl:call-template>
                  </fo:table-cell>
                </fo:table-row>
              </xsl:if>
              <xsl:if test="@securityClassification or @commercialClassification or @caveat">
                <fo:table-row keep-together="always">
                  <fo:table-cell number-columns-spanned="6">
                    <xsl:call-template name="add_security"/>
                  </fo:table-cell>
                </fo:table-row>
              </xsl:if>
              <xsl:if test="circuitBreakerAlts/@securityClassification or circuitBreakerAlts/@commercialClassification or circuitBreakerAlts/@caveat">
                <fo:table-row keep-together="always">
                  <fo:table-cell number-columns-spanned="6">
                    <xsl:call-template name="add_security">
                      <xsl:with-param name="sc" select="circuitBreakerAlts/@securityClassification"/>
                      <xsl:with-param name="cc" select="circuitBreakerAlts/@commercialClassification"/>
                      <xsl:with-param name="caveat" select="circuitBreakerAlts/@caveatClassification"/>
                    </xsl:call-template>
                  </fo:table-cell>
                </fo:table-row>
              </xsl:if>
              <xsl:apply-templates select="descendant::circuitBreaker"/>
              <xsl:if test="parent::__cgmark or __cgmark[child::circuitBreakerAlts]">
                <fo:table-row keep-together="always">
                  <fo:table-cell number-columns-spanned="6">                    
                    <fo:change-bar-end change-bar-class="{generate-id(.)}"/>
                  </fo:table-cell>
                </fo:table-row>
              </xsl:if>
            </xsl:for-each>
          </fo:table-body>
        </fo:table>
      </fo:block>
    </xsl:template>

    <xsl:template match="circuitBreaker">
      <fo:table-row>        
        <!-- label -->
        <xsl:apply-templates select="ancestor::circuitBreakerSpec/descendant::circuitBreakerIdent">
          <xsl:with-param name="idcgmark" select="generate-id(.)"/>
        </xsl:apply-templates>

        <!-- Name -->
        <fo:table-cell padding-left="2pt" padding-right="2pt">
          <fo:block text-align="left">
            <xsl:call-template name="style-para"/>
            <xsl:apply-templates select="ancestor::circuitBreakerSpec/name|ancestor::circuitBreakerSpec/__cgmark[child::name]|ancestor::circuitBreakerSpec/shortName|ancestor::circuitBreakerSpec/__cgmark[child::shortName]"/>
          </fo:block>
        </fo:table-cell>

        <!-- FIN, sementara tidak applicable-->
        <fo:table-cell padding-left="2pt" padding-right="2pt" text-align="center">
          <fo:block>
            <xsl:call-template name="style-para"/>
            <xsl:text>&#160;</xsl:text>
          </fo:block>
          <!-- <xsl:apply-templates select="ancestor::circuitBreakerSpec/circuitBreakerAlts/circuitBreaker/functionalItemRefGroup"/> -->
        </fo:table-cell>

        <!-- Group, sementara tidak applicable-->
        <fo:table-cell padding-left="2pt" padding-right="2pt" text-align="center">
          <fo:block>
            <xsl:call-template name="style-para"/>
            <xsl:text>&#160;</xsl:text>
          </fo:block>
          <!-- <xsl:apply-templates select="ancestor::circuitBreakerSpec/circuitBreakerRefGroup"/> -->
        </fo:table-cell>

        <!-- Ampere -->
        <fo:table-cell padding-left="2pt" padding-right="2pt" text-align="center">
          <fo:block>
            <xsl:call-template name="style-para"/>
            <xsl:apply-templates select="amperage"/>
          </fo:block>
        </fo:table-cell>

        <!-- Panel name -->
        <fo:table-cell padding-left="2pt" padding-right="2pt" text-align="center">
          <fo:block>
            <xsl:call-template name="style-para"/>
            <xsl:apply-templates select="quantity"/>
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </xsl:template>

    <xsl:template match="circuitBreakerIdent">
      <xsl:param name="idcgmark"/>
      <fo:table-cell padding-left="2pt" padding-right="2pt">

        <xsl:if test="parent::__cgmark">
          <fo:change-bar-begin change-bar-class="{$idcgmark}" change-bar-style="solid" change-bar-width="0.5pt" change-bar-offset="0.5cm"/>
        </xsl:if>

        <fo:block text-align="left">
          <xsl:call-template name="style-para"/>
          <xsl:call-template name="add_controlAuthority">
            <xsl:with-param name="id" select="ancestor::circuitBreakerSpec/circuitBreakerIdent/@controlAuthorityRefs"/>
          </xsl:call-template>
          <xsl:call-template name="add_security">
            <xsl:with-param name="sc" select="ancestor::circuitBreakerSpec/circuitBreakerIdent/@securityClassification"/>
            <xsl:with-param name="cc" select="ancestor::circuitBreakerSpec/circuitBreakerIdent/@commercialClassification"/>
            <xsl:with-param name="caveat" select="ancestor::circuitBreakerSpec/circuitBreakerIdent/@caveatClassification"/>
          </xsl:call-template>
          <xsl:value-of select="ancestor::circuitBreakerSpec/circuitBreakerIdent/@circuitBreakerNumber"/>
        </fo:block>

        <xsl:if test="parent::__cgmark">
          <fo:change-bar-end change-bar-class="{$idcgmark}"/>
        </xsl:if>
      </fo:table-cell>
    </xsl:template>

</xsl:transform>