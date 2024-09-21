<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:fox="http://xmlgraphics.apache.org/fop/extensions"
  xmlns:php="http://php.net/xsl">

  <!-- 
    Outstanding:
    1. verbatimText@verbatimStyle dan verbatimText@xml:space belum digunakan karena belum tau kegunaanya
    2. quantity@quantityTypeSpecifics belum digunakan karena TBD saja
    3. inlneSignificantData@significantParaDataType belum digunakan peruntukannya untuk pdf
   -->
  
  <xsl:template match="changeInline">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_inline_controlAuthority"/>
    <xsl:call-template name="add_inline_security"/>
    <fo:inline>
      <xsl:call-template name="add_id"/>
      <xsl:apply-templates/>
    </fo:inline>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template match="subScript">
    <fo:inline baseline-shift="sub" font-size="8pt">
      <xsl:apply-templates/>
    </fo:inline>
  </xsl:template>

  <xsl:template match="superScript">
    <fo:inline vertical-align="super" font-size="8pt">
      <xsl:apply-templates/>
    </fo:inline>
  </xsl:template>

  <!-- <xsl:template match="dmRef">
    <xsl:variable name="dmIdent" select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmIdent', ., '', '')"/>
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_inline_applicability"/>
    <xsl:call-template name="add_inline_controlAuthority"/>
    <xsl:call-template name="add_inline_security"/>
    <fo:basic-link internal-destination="{$dmIdent}" color="blue">
      <xsl:value-of select="$dmIdent"/>
    </fo:basic-link>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template> -->

  <!-- <xsl:template match="pmRef">
    <xsl:variable name="pmIdent" select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmIdent', ., '', '')"/>
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_inline_applicability"/>
    <xsl:call-template name="add_inline_controlAuthority"/>
    <xsl:call-template name="add_inline_security"/>
    <fo:basic-link internal-destination="{$pmIdent}" color="blue">
      <xsl:value-of select="$pmIdent"/>
    </fo:basic-link>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template> -->

  <!-- <xsl:template match="externalPubRef">
    <xsl:variable name="externalPubRefIdent" select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_externalPubRefIdent', ., '', '')"/>
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_inline_applicability"/>
    <xsl:call-template name="add_inline_controlAuthority"/>
    <xsl:call-template name="add_inline_security"/>
    <fo:basic-link external-destination="/{$pmIdent}" color="blue">
      <xsl:value-of select="$pmIdent"/>
    </fo:basic-link>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template> -->
  
  <!-- untuk merender anotasi danjuga footnote di bottom page atau di paragraphnya -->
  <xsl:template match="footnote[not(ancestor::table)]">
    <xsl:param name="position"><xsl:number level="any"/></xsl:param>
    <xsl:param name="onlyBottom" select="0"/>
    <xsl:variable name="mark">
      <xsl:value-of select="php:function('Ptdi\Mpub\Main\Helper::get_footnote_mark', number($position), string(@footnoteMark))"/>
    </xsl:variable>
    <fo:footnote font-size="8pt">
      <xsl:call-template name="add_applicability"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <fo:inline baseline-shift="super">
        <xsl:if test="not(boolean($onlyBottom))">
          <fo:basic-link text-decoration="underline" color="blue">
            <xsl:call-template name="add_id">
              <xsl:with-param name="force">yes</xsl:with-param>
              <xsl:with-param name="attributeName">internal-destination</xsl:with-param>
            </xsl:call-template>
            <xsl:value-of select="$mark"/>
          </fo:basic-link>
        </xsl:if>
      </fo:inline>
      <xsl:call-template name="cgmark_begin"/>
      <fo:footnote-body>
        <fo:block text-indent="-8pt" start-indent="8pt">
          <xsl:call-template name="add_id">
            <xsl:with-param name="force">yes</xsl:with-param>
          </xsl:call-template>
          <xsl:value-of select="$mark"/><xsl:text>&#160;&#160;</xsl:text>
          <xsl:apply-templates/>
        </fo:block>
        <xsl:call-template name="cgmark_end"/>
      </fo:footnote-body>
    </fo:footnote>
  </xsl:template>

  <!-- untuk merender anotasi footnote di table cell -->
  <xsl:template match="footnote[ancestor::table]|__cgmark[child::footnote]">
    <xsl:param name="position"><xsl:number level="any"/></xsl:param>
    <xsl:variable name="mark">
      <xsl:value-of select="php:function('Ptdi\Mpub\Main\Helper::get_footnote_mark', number($position), string(@footnoteMark))"/>
    </xsl:variable>
    <xsl:call-template name="cgmark_begin"/>
    <fo:basic-link internal-destination="{@id}">
      <fo:inline text-decoration="underline" color="blue" baseline-shift="super" font-size="8pt"><xsl:value-of select="$mark"/></fo:inline>
    </fo:basic-link>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <!-- dipanggil di fo:table-footer select="descendant::footnote"-->
  <xsl:template name="add_footnote">
    <xsl:param name="position"><xsl:number level="any"/></xsl:param>
    <xsl:variable name="mark">
      <xsl:value-of select="php:function('Ptdi\Mpub\Main\Helper::get_footnote_mark', number($position), string(@footnoteMark))"/>
    </xsl:variable>
    <xsl:call-template name="cgmark_begin"/>
    <fo:block text-indent="-8pt" start-indent="8pt" id="{@id}">
      <fo:inline><xsl:value-of select="$mark"/><xsl:text>&#160;&#160;</xsl:text></fo:inline>
      <xsl:apply-templates/>
    </fo:block>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template name="acronym">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_inline_controlAuthority"/>
    <xsl:call-template name="add_inline_security"/>
    <fo:inline>
      <xsl:call-template name="add_id"/>
      <xsl:apply-templates select="acronymTerm"/>
      <xsl:apply-templates select="acronymDefinition"/>
    </fo:inline>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <!-- nanti harusnya bisa ditambahkan link ke abbreviation (outside data module, but currently using @internalRefId) -->
  <xsl:template match="acronymTerm">
    <xsl:choose>
      <xsl:when test="following-sibling::acronymDefinition">
        <fo:inline>
          <xsl:apply-templates/>
        </fo:inline>
      </xsl:when>
      <xsl:otherwise>
        <fo:basic-link internal-destination="{string(@internalRefId)}" color="blue">
          <xsl:apply-templates/>
        </fo:basic-link>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="acronymDefinition">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_inline_controlAuthority"/>
    <xsl:call-template name="add_inline_security"/>
    <fo:inline>
      <xsl:call-template name="add_id"/>
      <xsl:text> (</xsl:text>
      <xsl:apply-templates/>
      <xsl:text>) </xsl:text>
    </fo:inline>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template match="verbatimText">
    <xsl:param name="verbatimStyle" select="string(@verbatimStyle)"/>
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_inline_controlAuthority"/>
    <xsl:call-template name="add_inline_security"/>
    <fo:inline>
      <xsl:attribute name="font-family">
        <xsl:value-of select="string(document($configurableValues_uri)//attr[@name='verbatimStyle' and @value=$verbatimStyle]/interpretation[@use='css']/style[@key='font-family']/@value)"/>
      </xsl:attribute>
      <xsl:call-template name="add_id"/>
      <xsl:apply-templates/>
    </fo:inline>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template match="emphasis">
    <fo:inline>
      <xsl:call-template name="setEmphasisAttribute"/>
      <xsl:apply-templates/>
    </fo:inline>
  </xsl:template>

  <xsl:template name="setEmphasisAttribute">
    <xsl:param name="emphasisType" select="string(@emphasisType)"/>
    <xsl:variable name="type" select="php:function('strtolower', php:function('Ptdi\Mpub\Transformer\Transformator::interpret','emphasisType', string($emphasisType), 'extension', 'default'))" />
    <xsl:choose>
      <xsl:when test="$type = 'bold'">
        <xsl:attribute name="font-weight">bold</xsl:attribute>
      </xsl:when>
      <xsl:when test="$type = 'italic'">
        <xsl:attribute name="font-style">italic</xsl:attribute>
      </xsl:when>
      <xsl:when test="$type = 'underline'">
        <xsl:attribute name="text-decoration">underline</xsl:attribute>
      </xsl:when>
      <xsl:when test="$type = 'strikethrough'">
        <xsl:attribute name="text-decoration">line-through</xsl:attribute>
      </xsl:when>
      <xsl:when test="$type = 'underline-bold'">
        <xsl:attribute name="text-decoration">underline</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
      </xsl:when>
      <xsl:when test="$type = 'underline-italic'">
        <xsl:attribute name="text-decoration">underline</xsl:attribute>
        <xsl:attribute name="font-style">italic</xsl:attribute>
      </xsl:when>
      <xsl:when test="$type = 'bold-italic'">
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="font-style">italic</xsl:attribute>
      </xsl:when>
      <xsl:when test="$type = 'bold-italic-underline'">
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="font-style">italic</xsl:attribute>
        <xsl:attribute name="text-decoration">underline</xsl:attribute>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="quantity">
    <fo:inline>
      <xsl:call-template name="add_inline_controlAuthority"/>
      <xsl:apply-templates/>
    </fo:inline>
  </xsl:template>

  <xsl:template match="quantityGroup">
    <fo:inline>
      <xsl:text>&#160;</xsl:text>
      <xsl:apply-templates/>
      <xsl:text>&#160;</xsl:text>      
    </fo:inline>
  </xsl:template>

  <xsl:template match="quantityValue">
    <fo:inline>
      <xsl:apply-templates select="quantityValue"/>
      <xsl:text>&#160;</xsl:text>
      <xsl:apply-templates/>
      <xsl:value-of select="@quantityUnitOfMeasure or parent::quantityGroup/@quantityUnitOfMeasure"/>
    </fo:inline>
  </xsl:template>

  <xsl:template match="quantityTolerance">
    <fo:inline>
      <xsl:choose>
        <xsl:when test="@quantityToleranceType = 'plus'">
          <xsl:text>+ </xsl:text>
        </xsl:when>
        <xsl:when test="@quantityToleranceType = 'minus'">
          <xsl:text>- </xsl:text>
        </xsl:when>
        <xsl:when test="@quantityToleranceType = 'plusorminus'">
          <xsl:text>&#177;&#160;</xsl:text>
        </xsl:when>
        <xsl:otherwise><xsl:text>&#160;</xsl:text></xsl:otherwise>
      </xsl:choose>
      <xsl:apply-templates/>
      <xsl:value-of select="@quantityUnitOfMeasure or parent::quantityGroup/@quantityUnitOfMeasure"/>
    </fo:inline>
  </xsl:template>

  <xsl:template match="inlineSignificantData">
    <fo:inline font-family="Calibri">
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_inline_controlAuthority"/>
      <xsl:apply-templates/>
      <xsl:call-template name="cgmark_end"/>
    </fo:inline>
  </xsl:template>

</xsl:transform>