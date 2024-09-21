<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template name="pageMasterByDefaultA4">
    <xsl:param name="masterName" select="$masterName"/>
    <fo:layout-master-set>
      <xsl:call-template name="get_simplePageMaster">
        <xsl:with-param name="masterName" select="$masterName"/>
      </xsl:call-template>
      <xsl:call-template name="get_pageSequenceMaster">
        <xsl:with-param name="masterName" select="$masterName"/>
        <xsl:with-param name="odd_masterReference">odd</xsl:with-param>
        <xsl:with-param name="even_masterReference">even</xsl:with-param>
        <xsl:with-param name="leftBlank_masterReference">left-blank</xsl:with-param>
      </xsl:call-template>
    </fo:layout-master-set>
  </xsl:template>

  <xsl:template name="getPmEntryTitle">
    <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::get_pmEntryTitle')"/>
  </xsl:template>

  <!-- ada lagi template get_applicability, tapi dugnakan untuk content, bukan header/footer -->
  <xsl:template name="getApplicabilityOnFooter">
    <xsl:param name="entry"/>
    <xsl:choose>
      <xsl:when test="$entry">
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::getApplicability', $entry//identAndStatusSection/dmStatus/applic)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::getApplicability', //identAndStatusSection/dmStatus/applic)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template name="getDMCode">
    <xsl:param name="entry"/>
    <xsl:choose>
      <xsl:when test="$entry">
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmCode', $entry//identAndStatusSection/dmAddress/dmIdent/dmCode)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmCode', //identAndStatusSection/dmAddress/dmIdent/dmCode)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="getPMCode">
    <xsl:param name="pm"/>
    <xsl:choose>
      <xsl:when test="$pm">
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmCode', $entry//identAndStatusSection/pmAddress/pmIdent/pmCode)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_pmCode', //identAndStatusSection/pmAddress/pmIdent/pmCode)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="getDate">
    <xsl:param name="entry"/>
    <xsl:choose>
      <xsl:when test="$entry">
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', $entry//identAndStatusSection/dmAddress/dmAddressItems/issueDate)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_issueDate', //identAndStatusSection/dmAddress/dmAddressItems/issueDate)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="getSecurity">
    <xsl:param name="entry"/>
    <xsl:choose>
      <xsl:when test="$entry">
        <xsl:apply-templates select="$entry//identAndStatusSection/dmStatus/security"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates select="//identAndStatusSection/dmStatus/security"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="get_logo">
    <xsl:param name="entry"/>
    <xsl:variable name="infoEntityPath">
      <xsl:text>url('</xsl:text>
      <!-- <xsl:text>file:///D:/Temporary/tesimage.png</xsl:text> -->
      <!-- <xsl:text>file:\\\D:\Temporary\tesimage.png</xsl:text> -->
      <!-- <xsl:text>file:\\\D:\Temporary/tesimage.png</xsl:text> -->
      <xsl:choose>
        <xsl:when test="$entry">
          <xsl:value-of select="unparsed-entity-uri($entry//dmStatus/logo/symbol/@infoEntityIdent)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="unparsed-entity-uri(//dmStatus/logo/symbol/@infoEntityIdent)"/>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:text>')</xsl:text>
    </xsl:variable>
    
    <fo:block>
      <fo:external-graphic src="{$infoEntityPath}" content-width="scale-to-fit" width="2.5cm"/>
    </fo:block>
  </xsl:template>
  
  <!-- reference orientation tidak digunakan melainkan langsung disesuaikan oleh setiap page master / master name nya masing-masing pmEntry -->
  <xsl:template name="get_simplePageMaster">
    <xsl:param name="unixId"/>
    <xsl:param name="masterName"/>
    <xsl:param name="length_unit"><xsl:call-template name="get_layout_unit_length"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>

    <!-- setelan defaultnya sama kayak default-A4.xsl -->
    <xsl:param name="width"><xsl:call-template name="get_layout_width"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="height"><xsl:call-template name="get_layout_height"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>

    <xsl:param name="masterName_for_odd"><xsl:call-template name="get_layout_masterName_for_odd"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param> <!-- ini dipakai untuk simple-page-master@master-name, lihat di bawah -->
    <xsl:param name="marginTop_for_odd"><xsl:call-template name="get_layout_marginTop_for_odd"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginBottom_for_odd"><xsl:call-template name="get_layout_marginBottom_for_odd"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginLeft_for_odd"><xsl:call-template name="get_layout_marginLeft_for_odd"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginRight_for_odd"><xsl:call-template name="get_layout_marginRight_for_odd"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginTop_for_odd_body"><xsl:call-template name="get_layout_marginTop_for_odd_body"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginBottom_for_odd_body"><xsl:call-template name="get_layout_marginBottom_for_odd_body"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginLeft_for_odd_body"><xsl:call-template name="get_layout_marginLeft_for_odd_body"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginRight_for_odd_body"><xsl:call-template name="get_layout_marginRight_for_odd_body"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="extent_for_odd_header"><xsl:call-template name="get_layout_extent_for_odd_header"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="extent_for_odd_footer"><xsl:call-template name="get_layout_extent_for_odd_footer"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>

    <!-- ini dipakai untuk simple-page-master@master-name, lihat dibawah -->
    <xsl:param name="masterName_for_even"><xsl:call-template name="get_layout_masterName_for_even"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginTop_for_even"><xsl:call-template name="get_layout_marginTop_for_even"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginBottom_for_even"><xsl:call-template name="get_layout_marginBottom_for_even"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginLeft_for_even"><xsl:call-template name="get_layout_marginLeft_for_even"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginRight_for_even"><xsl:call-template name="get_layout_marginRight_for_even"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginTop_for_even_body"><xsl:call-template name="get_layout_marginTop_for_even_body"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginBottom_for_even_body"><xsl:call-template name="get_layout_marginBottom_for_even_body"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginLeft_for_even_body"><xsl:call-template name="get_layout_marginLeft_for_even_body"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginRight_for_even_body"><xsl:call-template name="get_layout_marginRight_for_even_body"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="extent_for_even_header"><xsl:call-template name="get_layout_extent_for_even_header"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="extent_for_even_footer"><xsl:call-template name="get_layout_extent_for_even_footer"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>

    <!-- ini dipakai untuk simple-page-master@master-name, lihat dibawah -->
    <xsl:param name="masterName_for_leftBlank"><xsl:call-template name="get_layout_masterName_for_leftBlank"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginTop_for_leftBlank"><xsl:call-template name="get_layout_marginTop_for_leftBlank"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginBottom_for_leftBlank"><xsl:call-template name="get_layout_marginBottom_for_leftBlank"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginLeft_for_leftBlank"><xsl:call-template name="get_layout_marginLeft_for_leftBlank"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginRight_for_leftBlank"><xsl:call-template name="get_layout_marginRight_for_leftBlank"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginTop_for_leftBlank_body"><xsl:call-template name="get_layout_marginTop_for_leftBlank_body"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginBottom_for_leftBlank_body"><xsl:call-template name="get_layout_marginBottom_for_leftBlank_body"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginLeft_for_leftBlank_body"><xsl:call-template name="get_layout_marginLeft_for_leftBlank_body"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="marginRight_for_leftBlank_body"><xsl:call-template name="get_layout_marginRight_for_leftBlank_body"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="extent_for_leftBlank_header"><xsl:call-template name="get_layout_extent_for_leftBlank_header"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="extent_for_leftBlank_footer"><xsl:call-template name="get_layout_extent_for_leftBlank_footer"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>

    <!-- 
      jika berbeda pm/@pt, terlebih beda kertas/orientasi/width-height, region.xsl, pm.xsl, dmodule.xsl harus ditambahkan agar flow-name nya sesuai dengan region-name dan master-name
     -->
    <xsl:param name="regionName_for_body"><xsl:call-template name="get_layout_regionName_for_body"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param> <!-- ini akan di panggil di page-sequence, see pm.xsl or dmodule.xsl -->
    <xsl:param name="regionName_for_bodyLeftBlank"><xsl:call-template name="get_layout_regionName_for_bodyLeftBlank"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param> <!-- sejauh ini, ini belum digunakan karena tulisan 'intentionally left blank' akan ditulis di static-content header, see region.xsl -->
    <xsl:param name="regionName_for_headerOdd"><xsl:call-template name="get_layout_regionName_for_headerOdd"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param> <!-- ini dipanggil di static-content, see region.xsl -->
    <xsl:param name="regionName_for_footerOdd"><xsl:call-template name="get_layout_regionName_for_footerOdd"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param> <!-- ini dipanggil di static-content, see region.xsl -->
    <xsl:param name="regionName_for_headerEven"><xsl:call-template name="get_layout_regionName_for_headerEven"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param> <!-- ini dipanggil di static-content, see region.xsl -->
    <xsl:param name="regionName_for_footerEven"><xsl:call-template name="get_layout_regionName_for_footerEven"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param> <!-- ini dipanggil di static-content, see region.xsl -->
    <xsl:param name="regionName_for_headerLeftBlank"><xsl:call-template name="get_layout_regionName_for_headerLeftBlank"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param> <!-- ini dipanggil di static-content, see region.xsl -->
    <xsl:param name="regionName_for_footerLeftBlank"><xsl:call-template name="get_layout_regionName_for_footerLeftBlank"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param> <!-- ini dipanggil di static-content, see region.xsl -->

    <!-- <xsl:variable name="orient"><xsl:call-template name="get_orientation"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:variable> -->
    <!-- <xsl:variable name="orient">land</xsl:variable> -->
    <!-- <xsl:variable name="orient"><xsl:call-template name="get_orientation"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:variable> -->
    <xsl:variable name="orient" select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/@orientation)"/>
    <!-- <xsl:value-of select="php:function('dd',$orient)"/> -->
    <!-- nyontek ke default-A4.xsl -->
    <xsl:choose>
      <xsl:when test="$orient = 'port'">
        <fo:simple-page-master master-name="{$masterName_for_odd}" page-height="{concat($height, $length_unit)}" page-width="{concat($width, $length_unit)}" margin-top="{concat($marginTop_for_odd,$length_unit)}" margin-bottom="{concat($marginBottom_for_odd,$length_unit)}" margin-left="{concat($marginLeft_for_odd, $length_unit)}" margin-right="{concat($marginRight_for_odd, $length_unit)}">
          <fo:region-body region-name="{$regionName_for_body}" margin-top="{concat($marginTop_for_odd_body, $length_unit)}" margin-bottom="{concat($marginBottom_for_odd_body, $length_unit)}"/>
          <fo:region-before region-name="{$regionName_for_headerOdd}" extent="{concat($extent_for_odd_header, $length_unit)}" />
          <fo:region-after region-name="{$regionName_for_footerOdd}" extent="{concat($extent_for_odd_footer,$length_unit)}" />
        </fo:simple-page-master>
        <fo:simple-page-master master-name="{$masterName_for_even}" page-height="{concat($height, $length_unit)}" page-width="{concat($width, $length_unit)}" margin-top="{concat($marginTop_for_even, $length_unit)}" margin-bottom="{concat($marginBottom_for_even, $length_unit)}" margin-left="{concat($marginLeft_for_even, $length_unit)}" margin-right="{concat($marginRight_for_even, $length_unit)}">
          <fo:region-body region-name="{$regionName_for_body}" margin-top="{concat($marginTop_for_even_body, $length_unit)}" margin-bottom="{concat($marginBottom_for_even_body, $length_unit)}"/>
          <fo:region-before region-name="{$regionName_for_headerEven}" extent="{concat($extent_for_even_header, $length_unit)}"/>
          <fo:region-after region-name="{$regionName_for_footerEven}" extent="{concat($extent_for_even_footer, $length_unit)}"/>
        </fo:simple-page-master>
        <fo:simple-page-master master-name="{$masterName_for_leftBlank}" page-height="{concat($height, $length_unit)}" page-width="{concat($width, $length_unit)}" margin-top="{concat($marginTop_for_leftBlank, $length_unit)}" margin-bottom="{concat($marginBottom_for_leftBlank, $length_unit)}" margin-left="{concat($marginLeft_for_leftBlank, $length_unit)}" margin-right="{concat($marginRight_for_leftBlank, $length_unit)}">
          <fo:region-body region-name="{$regionName_for_bodyLeftBlank}" margin-top="{concat($marginTop_for_leftBlank_body, $length_unit)}" margin-bottom="{concat($marginBottom_for_leftBlank_body, $length_unit)}"/>
          <fo:region-before region-name="{$regionName_for_headerLeftBlank}" extent="{concat($extent_for_leftBlank_header, $length_unit)}" />
          <fo:region-after region-name="{$regionName_for_footerLeftBlank}" extent="{concat($extent_for_leftBlank_footer, $length_unit)}" />
        </fo:simple-page-master>
        <!-- <fo:simple-page-master master-name="{$masterName_for_odd}" page-height="{concat($height, $length_unit)}" page-width="{concat($width, $length_unit)}" margin-top="{concat($marginTop_for_odd,$length_unit)}" margin-bottom="{concat($marginBottom_for_odd,$length_unit)}" margin-left="{concat($marginLeft_for_odd, $length_unit)}" margin-right="{concat($marginRight_for_odd, $length_unit)}">
          <fo:region-body region-name="{$regionName_for_body}" margin-top="{concat($marginTop_for_odd_body, $length_unit)}" margin-bottom="{concat($marginBottom_for_odd_body, $length_unit)}"/>
          <fo:region-before region-name="{$regionName_for_headerOdd}" extent="{concat($extent_for_odd_header, $length_unit)}" />
          <fo:region-after region-name="{$regionName_for_footerOdd}" extent="{concat($extent_for_odd_footer,$length_unit)}" />
          <fo:region-start region-name="aaa"/>
          <fo:region-end region-name="bbb"/>
        </fo:simple-page-master>
        <fo:simple-page-master master-name="{$masterName_for_even}" page-height="{concat($height, $length_unit)}" page-width="{concat($width, $length_unit)}" margin-top="{concat($marginTop_for_even, $length_unit)}" margin-bottom="{concat($marginBottom_for_even, $length_unit)}" margin-left="{concat($marginLeft_for_even, $length_unit)}" margin-right="{concat($marginRight_for_even, $length_unit)}">
          <fo:region-body region-name="{$regionName_for_body}" margin-top="{concat($marginTop_for_even_body, $length_unit)}" margin-bottom="{concat($marginBottom_for_even_body, $length_unit)}"/>
          <fo:region-before region-name="{$regionName_for_headerEven}" extent="{concat($extent_for_even_header, $length_unit)}"/>
          <fo:region-after region-name="{$regionName_for_footerEven}" extent="{concat($extent_for_even_footer, $length_unit)}"/>
          <fo:region-start region-name="ccc"/>
          <fo:region-end region-name="ddd"/>
        </fo:simple-page-master>
        <fo:simple-page-master master-name="{$masterName_for_leftBlank}" page-height="{concat($height, $length_unit)}" page-width="{concat($width, $length_unit)}" margin-top="{concat($marginTop_for_leftBlank, $length_unit)}" margin-bottom="{concat($marginBottom_for_leftBlank, $length_unit)}" margin-left="{concat($marginLeft_for_leftBlank, $length_unit)}" margin-right="{concat($marginRight_for_leftBlank, $length_unit)}">
          <fo:region-body region-name="{$regionName_for_bodyLeftBlank}" margin-top="{concat($marginTop_for_leftBlank_body, $length_unit)}" margin-bottom="{concat($marginBottom_for_leftBlank_body, $length_unit)}"/>
          <fo:region-before region-name="{$regionName_for_headerLeftBlank}" extent="{concat($extent_for_leftBlank_header, $length_unit)}" />
          <fo:region-after region-name="{$regionName_for_footerLeftBlank}" extent="{concat($extent_for_leftBlank_footer, $length_unit)}" />
          <fo:region-start region-name="eee"/>
          <fo:region-end region-name="fff"/>
        </fo:simple-page-master> -->
      </xsl:when>
      <xsl:otherwise>
        <fo:simple-page-master master-name="{$masterName_for_odd}" page-height="{concat($height, $length_unit)}" page-width="{concat($width, $length_unit)}" margin-top="{concat($marginTop_for_odd,$length_unit)}" margin-bottom="{concat($marginBottom_for_odd,$length_unit)}" margin-left="{concat($marginLeft_for_odd, $length_unit)}" margin-right="{concat($marginRight_for_odd, $length_unit)}">
          <fo:region-body region-name="{$regionName_for_body}" margin-right="{concat($marginRight_for_odd_body, $length_unit)}" margin-left="{concat($marginLeft_for_odd_body, $length_unit)}"/>
          <fo:region-start region-name="{$regionName_for_footerOdd}" extent="{concat($extent_for_odd_footer,$length_unit)}" />
          <fo:region-end region-name="{$regionName_for_headerOdd}" extent="{concat($extent_for_odd_header, $length_unit)}" />
        </fo:simple-page-master>
        <fo:simple-page-master master-name="{$masterName_for_even}" page-height="{concat($height, $length_unit)}" page-width="{concat($width, $length_unit)}" margin-top="{concat($marginTop_for_even, $length_unit)}" margin-bottom="{concat($marginBottom_for_even, $length_unit)}" margin-left="{concat($marginLeft_for_even, $length_unit)}" margin-right="{concat($marginRight_for_even, $length_unit)}">
          <fo:region-body region-name="{$regionName_for_body}" margin-right="{concat($marginRight_for_even_body, $length_unit)}" margin-left="{concat($marginLeft_for_even_body, $length_unit)}"/>
          <fo:region-start region-name="{$regionName_for_footerEven}" extent="{concat($extent_for_even_footer, $length_unit)}"/>
          <fo:region-end region-name="{$regionName_for_headerEven}" extent="{concat($extent_for_even_header, $length_unit)}"/>
        </fo:simple-page-master>
        <fo:simple-page-master master-name="{$masterName_for_leftBlank}" page-height="{concat($height, $length_unit)}" page-width="{concat($width, $length_unit)}" margin-top="{concat($marginTop_for_leftBlank, $length_unit)}" margin-bottom="{concat($marginBottom_for_leftBlank, $length_unit)}" margin-left="{concat($marginLeft_for_leftBlank, $length_unit)}" margin-right="{concat($marginRight_for_leftBlank, $length_unit)}">
          <fo:region-body region-name="{$regionName_for_bodyLeftBlank}" margin-right="{concat($marginRight_for_leftBlank_body, $length_unit)}" margin-left="{concat($marginLeft_for_leftBlank_body, $length_unit)}"/>
          <fo:region-start region-name="{$regionName_for_footerLeftBlank}" extent="{concat($extent_for_leftBlank_footer, $length_unit)}" />
          <fo:region-end region-name="{$regionName_for_headerLeftBlank}" extent="{concat($extent_for_leftBlank_header, $length_unit)}" />
        </fo:simple-page-master>
        <!-- <fo:simple-page-master master-name="{$masterName_for_odd}" page-height="{concat($height, $length_unit)}" page-width="{concat($width, $length_unit)}" margin-top="{concat($marginTop_for_odd,$length_unit)}" margin-bottom="{concat($marginBottom_for_odd,$length_unit)}" margin-left="{concat($marginLeft_for_odd, $length_unit)}" margin-right="{concat($marginRight_for_odd, $length_unit)}">
          <fo:region-body region-name="{$regionName_for_body}" margin-right="{concat($marginRight_for_odd_body, $length_unit)}" margin-left="{concat($marginLeft_for_odd_body, $length_unit)}"/>
          <fo:region-before region-name="ggg"/>
          <fo:region-after region-name="hhh"/>
          <fo:region-start region-name="{$regionName_for_footerOdd}" extent="{concat($extent_for_odd_footer,$length_unit)}" />
          <fo:region-end region-name="{$regionName_for_headerOdd}" extent="{concat($extent_for_odd_header, $length_unit)}" />
        </fo:simple-page-master>
        <fo:simple-page-master master-name="{$masterName_for_even}" page-height="{concat($height, $length_unit)}" page-width="{concat($width, $length_unit)}" margin-top="{concat($marginTop_for_even, $length_unit)}" margin-bottom="{concat($marginBottom_for_even, $length_unit)}" margin-left="{concat($marginLeft_for_even, $length_unit)}" margin-right="{concat($marginRight_for_even, $length_unit)}">
          <fo:region-body region-name="{$regionName_for_body}" margin-right="{concat($marginRight_for_even_body, $length_unit)}" margin-left="{concat($marginLeft_for_even_body, $length_unit)}"/>
          <fo:region-before region-name="iii"/>
          <fo:region-after region-name="jjj"/>
          <fo:region-start region-name="{$regionName_for_footerEven}" extent="{concat($extent_for_even_footer, $length_unit)}"/>
          <fo:region-end region-name="{$regionName_for_headerEven}" extent="{concat($extent_for_even_header, $length_unit)}"/>
        </fo:simple-page-master>
        <fo:simple-page-master master-name="{$masterName_for_leftBlank}" page-height="{concat($height, $length_unit)}" page-width="{concat($width, $length_unit)}" margin-top="{concat($marginTop_for_leftBlank, $length_unit)}" margin-bottom="{concat($marginBottom_for_leftBlank, $length_unit)}" margin-left="{concat($marginLeft_for_leftBlank, $length_unit)}" margin-right="{concat($marginRight_for_leftBlank, $length_unit)}">
          <fo:region-body region-name="{$regionName_for_bodyLeftBlank}" margin-right="{concat($marginRight_for_leftBlank_body, $length_unit)}" margin-left="{concat($marginLeft_for_leftBlank_body, $length_unit)}"/>
          <fo:region-before region-name="kkk"/>
          <fo:region-after region-name="lll"/>
          <fo:region-start region-name="{$regionName_for_footerLeftBlank}" extent="{concat($extent_for_leftBlank_footer, $length_unit)}" />
          <fo:region-end region-name="{$regionName_for_headerLeftBlank}" extent="{concat($extent_for_leftBlank_header, $length_unit)}" />
        </fo:simple-page-master> -->
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!-- defaultnya pakai intentionally left blank -->
  <xsl:template name="get_pageSequenceMaster">
    <xsl:param name="unixId"/>
    <xsl:param name="masterName">default-pm</xsl:param>
    <xsl:param name="odd_masterReference"><xsl:call-template name="get_layout_masterName_for_odd"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="even_masterReference"><xsl:call-template name="get_layout_masterName_for_even"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <xsl:param name="leftBlank_masterReference"><xsl:call-template name="get_layout_masterName_for_leftBlank"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template></xsl:param>
    <!-- nyontek ke default-A4.xsl -->
    <fo:page-sequence-master master-name="{$masterName}">
      <fo:repeatable-page-master-alternatives>
        <!-- untuk page between first and last -->
        <fo:conditional-page-master-reference master-reference="{concat($odd_masterReference, $unixId)}" page-position="rest" odd-or-even="odd"/>
        <fo:conditional-page-master-reference master-reference="{concat($even_masterReference, $unixId)}" page-position="rest" odd-or-even= "even"/>

        <!-- for the first page -->
        <fo:conditional-page-master-reference master-reference="{concat($odd_masterReference, $unixId)}" page-position="first" odd-or-even="odd"/>

        <!-- 
          for the end page 1. last, even, and blank akan mencetak intentionally left blank
          kalau 2. last, even, and not blank tidak akan mencetak intentionally left blank
        -->
        <fo:conditional-page-master-reference master-reference="{concat($leftBlank_masterReference, $unixId)}" page-position="last" odd-or-even="even" blank-or-not-blank="blank"/>
        <fo:conditional-page-master-reference master-reference="{concat($even_masterReference, $unixId)}" page-position="last" odd-or-even="even"/>    
      </fo:repeatable-page-master-alternatives>
    </fo:page-sequence-master>
  </xsl:template>
</xsl:transform>