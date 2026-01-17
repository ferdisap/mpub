<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <!-- 
    Outstanding
    1. element numberOfPages belum digunakan
    2. simplePara[parent::footnoteRemarks] tidak boleh digunakan karena space column yang tidak ada
    3. setiap frontMatterSublist berisi table dengan jumlah/width column yang sama semua. Jadi kalau mau beda2 seperti HIGHm=, LEODM harus menggunakan data module yang berbeda dengan parent::frontMatterList@frontMatterType berbeda pula
    4. title pada setiap front matter entry tidak di buat
   -->

  <xsl:template match="frontMatterList">
    <xsl:param name="frontMatterType" select="string(@frontMatterType)"/>
    <xsl:variable name="type">
      <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','frontMatterType', string($frontMatterType), 'abbreviation', 'default')" />
    </xsl:variable>
    <xsl:call-template name="add_applicability"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:choose>
      <xsl:when test="$type = 'leodm'"><xsl:call-template name="leodm">
        <xsl:with-param name="type" select="$type"/>
      </xsl:call-template></xsl:when>
      <xsl:when test="$type = 'highlights'"><xsl:call-template name="highlights">
        <xsl:with-param name="type" select="$type"/>
      </xsl:call-template></xsl:when>
      <xsl:when test="$type = 'highlights and updating instruction'"><xsl:call-template name="highlights_and_updating">
        <xsl:with-param name="type" select="$type"/>
      </xsl:call-template></xsl:when>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="issueTypeUPDT">
    <xsl:variable name="issueType"><xsl:value-of select="@issueType" /></xsl:variable>
    <xsl:choose>
      <xsl:when test="$issueType = 'new'">I</xsl:when>
      <xsl:when test="$issueType = 'revised'">R,I</xsl:when>
      <xsl:when test="$issueType = 'changed'">R,I</xsl:when>
      <xsl:when test="$issueType = 'deleted'">R</xsl:when>
      <xsl:when test="$issueType = 'status'">R,I</xsl:when>
      <xsl:when test="$issueType = 'reinstate-status'">I</xsl:when>
      <xsl:when test="$issueType = 'reinstate-changed'">I</xsl:when>
      <xsl:when test="$issueType = 'reinstate-revised'">I</xsl:when>
      <xsl:otherwise><xsl:value-of select="$issueType" /></xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template name="get_code_frontMatterEntry">
    <xsl:param name="name" select="name(.)"/>
    <xsl:param name="entry"/>
    <xsl:choose>
      <xsl:when test="$name = 'frontMatterDmEntry'">
        <xsl:choose>
          <xsl:when test="dmRef/dmRefIdent/dmCode">          
            <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmCode', dmRef/dmRefIdent/dmCode)" />
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmCode', $entry//descendant::dmIdent/dmCode)" />
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="$name = 'frontMatterPmEntry'">
        <xsl:choose>
          <xsl:when test="pmRef/pmRefIdent/pmCode">          
            <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmCode', pmRef/pmRefIdent/pmCode)" />
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmCode', $entry//descendant::pmIdent/pmCode)" />
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="$name = 'frontMatterExternalPubEntry'">
        <xsl:value-of select="externalPubRef/descendant::externalPubCode"/>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="get_dateno_frontMatterEntry">
    <xsl:param name="name" select="name(.)"/>
    <xsl:param name="entry"/>
    <xsl:choose>
      <xsl:when test="$name = 'frontMatterDmEntry'">
        <xsl:choose>
          <xsl:when test="dmRef/dmRefAddressItems/issueDate">
            <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', dmRef/dmRefAddressItems/issueDate)" />
            <xsl:text>/ </xsl:text>
            <xsl:value-of select="dmRef/dmRefIdent/issueInfo/@issueNumber" />        
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', $entry//descendant::dmAddressItems/issueDate)" />
            <xsl:text>/ </xsl:text>
            <xsl:value-of select="$entry//descendant::dmAddress/dmIdent/issueInfo/@issueNumber" />
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="$name = 'frontMatterPmEntry'">
        <xsl:choose>
          <xsl:when test="pmRef/pmRefAddressItems/issueDate">
            <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', pmRef/pmRefAddressItems/issueDate)" />
            <xsl:text>/ </xsl:text>
            <xsl:value-of select="pmRef/pmRefIdent/issueInfo/@issueNumber" />        
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', $entry//descendant::pmAddressItems/issueDate)" />
            <xsl:text>/ </xsl:text>
            <xsl:value-of select="$entry//descendant::pmAddress/pmIdent/issueInfo/@issueNumber" />
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="$name = 'frontMatterExternalPubEntry'">
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', externalPubRef/descendant::externalPubIssueDate)" />
        <xsl:text>/ </xsl:text>
        <xsl:apply-templates select="externalPubRef/descendant::externalPubIssue" />
      </xsl:when>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template name="get_title_frontMatterEntry">
    <xsl:param name="name" select="name(.)"/>
    <xsl:param name="entry"/>
    <xsl:choose>
      <xsl:when test="$name = 'frontMatterDmEntry'">
        <xsl:choose>
          <xsl:when test="dmRef/dmRefAddressItems/dmTitle">
            <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_title', dmRef/dmRefAddressItems/dmTitle)"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_title', $entry//dmAddressItems/dmTitle)"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="$name = 'frontMatterPmEntry'">
        <xsl:choose>
          <xsl:when test="pmRef/pmRefAddressItems/pmTitle">
            <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', pmRef/pmRefAddressItems/pmTitle)"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_title', $entry//pmAddressItems/pmTitle)"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>   
      <xsl:when test="$name = 'frontMatterExternalPubEntry'">
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', externalPubRef/descendant::externalPubIssueDate)" />
        <xsl:text>/ </xsl:text>
        <xsl:apply-templates select="externalPubRef/descendant::externalPubTitle" />
      </xsl:when>
    </xsl:choose>
  </xsl:template>

  </xsl:transform>