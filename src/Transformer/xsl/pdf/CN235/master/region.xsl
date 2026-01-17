<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">
  
  <xsl:template name="header">
    <xsl:param name="masterName"/>
    <xsl:param name="oddOrEven"/>
    <xsl:param name="pagePosition"/>
    <xsl:param name="entry"/>
    <xsl:choose>
      <xsl:when test="$masterName = 'default-A4'">
        <xsl:choose>
          <xsl:when test="$oddOrEven = 'odd'">
            <xsl:call-template name="header-odd-default-A4">
              <xsl:with-param name="masterName" select="$masterName"/>
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </xsl:when>
          <xsl:when test="$oddOrEven = 'even'">
            <xsl:call-template name="header-even-default-A4">
              <xsl:with-param name="masterName" select="$masterName"/>
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </xsl:when>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="$masterName = 'default-A4L'">
        <xsl:choose>
          <xsl:when test="$oddOrEven = 'odd'">
            <xsl:call-template name="header-odd-default-A4L">
              <xsl:with-param name="masterName" select="$masterName"/>
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </xsl:when>
          <xsl:when test="$oddOrEven = 'even'">
            <xsl:call-template name="header-even-default-A4L">
              <xsl:with-param name="masterName" select="$masterName"/>
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </xsl:when>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="$masterName = 'default-pm'">
        <xsl:choose>
          <xsl:when test="$oddOrEven = 'odd'">
            <xsl:call-template name="header-odd-default-A4">
              <xsl:with-param name="masterName" select="$masterName"/>
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </xsl:when>
          <xsl:when test="$oddOrEven = 'even'">
            <xsl:call-template name="header-even-default-A4">
              <xsl:with-param name="masterName" select="$masterName"/>
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </xsl:when>
        </xsl:choose>
      </xsl:when>
      <!-- ini nanti diubah, mungkin header-footer nya tidak relevan lagi pakai yang default A4 kalau pakai kertas A5-->
      <xsl:when test="$masterName = 'poh'">
        <xsl:choose>
          <xsl:when test="$oddOrEven = 'odd'">
            <xsl:call-template name="header-odd-default-A4">
              <xsl:with-param name="masterName" select="$masterName"/>
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </xsl:when>
          <xsl:when test="$oddOrEven = 'even'">
            <xsl:call-template name="header-even-default-A4">
              <xsl:with-param name="masterName" select="$masterName"/>
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </xsl:when>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="$masterName = 'maintPlanning'">
        <xsl:choose>
          <xsl:when test="$oddOrEven = 'odd'">
            <xsl:call-template name="header-odd-default-A4L">
              <xsl:with-param name="masterName" select="$masterName"/>
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </xsl:when>
          <xsl:when test="$oddOrEven = 'even'">
            <xsl:call-template name="header-even-default-A4L">
              <xsl:with-param name="masterName" select="$masterName"/>
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </xsl:when>
        </xsl:choose>
      </xsl:when>
      <xsl:otherwise>
        <fo:block>&#160;</fo:block>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="footer">
    <xsl:param name="id"/>
    <xsl:param name="masterName"/>
    <xsl:param name="oddOrEven"/>
    <xsl:param name="entry"/>
    <xsl:choose>
      <xsl:when test="$masterName = 'default-A4'">
        <xsl:if test="$oddOrEven = 'odd'">
          <xsl:call-template name="footer-odd-default-A4">
            <xsl:with-param name="masterName" select="$masterName"/>
            <xsl:with-param name="id" select="$id"/>
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </xsl:if>
        <xsl:if test="$oddOrEven = 'even'">
          <xsl:call-template name="footer-even-default-A4">
            <xsl:with-param name="masterName" select="$masterName"/>
            <xsl:with-param name="id" select="$id"/>
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </xsl:if>
      </xsl:when>
      <xsl:when test="$masterName = 'default-A4L'">
        <xsl:if test="$oddOrEven = 'odd'">
          <xsl:call-template name="footer-odd-default-A4L">
            <xsl:with-param name="masterName" select="$masterName"/>
            <xsl:with-param name="id" select="$id"/>
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </xsl:if>
        <xsl:if test="$oddOrEven = 'even'">
          <xsl:call-template name="footer-even-default-A4L">
            <xsl:with-param name="masterName" select="$masterName"/>
            <xsl:with-param name="id" select="$id"/>
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </xsl:if>
      </xsl:when>
      <xsl:when test="$masterName = 'default-pm'">
        <xsl:if test="$oddOrEven = 'odd'">
          <xsl:call-template name="footer-odd-default-A4">
            <xsl:with-param name="masterName" select="$masterName"/>
            <xsl:with-param name="id" select="$id"/>
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </xsl:if>
        <xsl:if test="$oddOrEven = 'even'">
          <xsl:call-template name="footer-even-default-A4">
            <xsl:with-param name="masterName" select="$masterName"/>
            <xsl:with-param name="id" select="$id"/>
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </xsl:if>
      </xsl:when>
      <!-- ini nanti diubah, mungkin header-footer nya tidak relevan lagi pakai yang default A4 kalau pakai kertas A5 -->
      <xsl:when test="$masterName = 'poh'">
        <xsl:if test="$oddOrEven = 'odd'">
          <xsl:call-template name="footer-odd-default-A4">
            <xsl:with-param name="masterName" select="$masterName"/>
            <xsl:with-param name="id" select="$id"/>
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </xsl:if>
        <xsl:if test="$oddOrEven = 'even'">
          <xsl:call-template name="footer-even-default-A4">
            <xsl:with-param name="masterName" select="$masterName"/>
            <xsl:with-param name="id" select="$id"/>
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </xsl:if>
      </xsl:when>
      <xsl:when test="$masterName = 'maintPlanning'">
        <xsl:if test="$oddOrEven = 'odd'">
          <xsl:call-template name="footer-odd-default-A4L">
            <xsl:with-param name="masterName" select="$masterName"/>
            <xsl:with-param name="id" select="$id"/>
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </xsl:if>
        <xsl:if test="$oddOrEven = 'even'">
          <xsl:call-template name="footer-even-default-A4L">
            <xsl:with-param name="masterName" select="$masterName"/>
            <xsl:with-param name="id" select="$id"/>
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
        </xsl:if>
      </xsl:when>
      <xsl:otherwise>
        <fo:block>&#160;</fo:block>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
    
  <xsl:template name="headerleftBlank">
    <xsl:param name="masterName"/>
    <xsl:param name="entry"/>
    <xsl:call-template name="header">
      <xsl:with-param name="masterName" select="$masterName"/>
      <xsl:with-param name="oddOrEven">even</xsl:with-param>
      <xsl:with-param name="entry" select="$entry"/>
    </xsl:call-template>
    <fo:block-container position="fixed">
      <xsl:choose>
        <xsl:when test="$masterName = 'default-pm' or $masterName = 'default-A4'">
          <xsl:attribute name="top">14cm</xsl:attribute>
          <xsl:attribute name="left">7cm</xsl:attribute>
        </xsl:when>
        <xsl:when test="$masterName = 'poh'">
          <xsl:attribute name="top">10cm</xsl:attribute>
          <xsl:attribute name="left">4cm</xsl:attribute>
        </xsl:when>
        <xsl:when test="$masterName = 'maintPlanning'">
          <xsl:attribute name="top">9cm</xsl:attribute>
          <xsl:attribute name="left">12cm</xsl:attribute>
        </xsl:when>
        <xsl:otherwise>
          <!-- TBD: jika mastername berbeda-beda, sesuai kertas dan orientasi kertas masing-masing -->
        </xsl:otherwise>
      </xsl:choose>
      <fo:block>Intentionally left blank</fo:block>
    </fo:block-container>
  </xsl:template>

  <!-- dipanggil di fo:page-sequence, cek dmodule.xsl atau pm.xsl -->
  <xsl:template name="getRegion">
      <xsl:param name="masterReference"/>
      <xsl:param name="id"/>
      <xsl:param name="entry"/>

      <xsl:variable name="regionName_for_body"><xsl:call-template name="get_layout_regionName_for_body"><xsl:with-param name="masterName" select="$masterReference"/></xsl:call-template></xsl:variable> <!-- ini akan di panggil di page-sequence, see pm.xsl or dmodule.xsl -->
      <xsl:variable name="regionName_for_bodyLeftBlank"><xsl:call-template name="get_layout_regionName_for_bodyLeftBlank"><xsl:with-param name="masterName" select="$masterReference"/></xsl:call-template></xsl:variable> <!-- sejauh ini, ini belum digunakan karena tulisan 'intentionally left blank' akan ditulis di static-content header, see region.xsl -->
      <xsl:variable name="regionName_for_headerOdd"><xsl:call-template name="get_layout_regionName_for_headerOdd"><xsl:with-param name="masterName" select="$masterReference"/></xsl:call-template></xsl:variable> <!-- ini dipanggil di static-content, see region.xsl -->
      <xsl:variable name="regionName_for_footerOdd"><xsl:call-template name="get_layout_regionName_for_footerOdd"><xsl:with-param name="masterName" select="$masterReference"/></xsl:call-template></xsl:variable> <!-- ini dipanggil di static-content, see region.xsl -->
      <xsl:variable name="regionName_for_headerEven"><xsl:call-template name="get_layout_regionName_for_headerEven"><xsl:with-param name="masterName" select="$masterReference"/></xsl:call-template></xsl:variable> <!-- ini dipanggil di static-content, see region.xsl -->
      <xsl:variable name="regionName_for_footerEven"><xsl:call-template name="get_layout_regionName_for_footerEven"><xsl:with-param name="masterName" select="$masterReference"/></xsl:call-template></xsl:variable> <!-- ini dipanggil di static-content, see region.xsl -->
      <xsl:variable name="regionName_for_headerLeftBlank"><xsl:call-template name="get_layout_regionName_for_headerLeftBlank"><xsl:with-param name="masterName" select="$masterReference"/></xsl:call-template></xsl:variable> <!-- ini dipanggil di static-content, see region.xsl -->
      <xsl:variable name="regionName_for_footerLeftBlank"><xsl:call-template name="get_layout_regionName_for_footerLeftBlank"><xsl:with-param name="masterName" select="$masterReference"/></xsl:call-template></xsl:variable> <!-- ini dipanggil di static-content, see region.xsl -->

      <fo:static-content flow-name="{$regionName_for_headerOdd}">
        <xsl:call-template name="header">
          <xsl:with-param name="masterName" select="$masterReference"/>
          <xsl:with-param name="oddOrEven" select="'odd'"/>
          <xsl:with-param name="entry" select="$entry"/>
        </xsl:call-template>
      </fo:static-content>
      <fo:static-content flow-name="{$regionName_for_headerEven}">
        <xsl:call-template name="header">
          <xsl:with-param name="masterName" select="$masterReference"/>
          <xsl:with-param name="oddOrEven" select="'even'"/>
          <xsl:with-param name="entry" select="$entry"/>
        </xsl:call-template>
      </fo:static-content>
      <fo:static-content flow-name="{$regionName_for_footerOdd}">
        <xsl:call-template name="footer">
          <xsl:with-param name="id" select="$id"/>
          <xsl:with-param name="masterName" select="$masterReference"/>
          <xsl:with-param name="oddOrEven" select="'odd'"/>
          <xsl:with-param name="entry" select="$entry"/>
        </xsl:call-template>
      </fo:static-content>
      <fo:static-content flow-name="{$regionName_for_footerEven}">
        <xsl:call-template name="footer">
          <xsl:with-param name="id" select="$id"/>
          <xsl:with-param name="masterName" select="$masterReference"/>
          <xsl:with-param name="oddOrEven" select="'even'"/>
          <xsl:with-param name="entry" select="$entry"/>
        </xsl:call-template>
      </fo:static-content>
      <fo:static-content flow-name="{$regionName_for_headerLeftBlank}">
        <xsl:call-template name="headerleftBlank">
          <xsl:with-param name="masterName" select="$masterReference"/>
          <xsl:with-param name="entry" select="$entry"/>
        </xsl:call-template>
      </fo:static-content>
      <fo:static-content flow-name="{$regionName_for_footerLeftBlank}">
        <xsl:call-template name="footer">
          <xsl:with-param name="id" select="$id"/>
          <xsl:with-param name="masterName" select="$masterReference"/>
          <xsl:with-param name="oddOrEven" select="'even'"/>
          <xsl:with-param name="entry" select="$entry"/>
        </xsl:call-template>
      </fo:static-content>
      <fo:static-content flow-name="xsl-footnote-separator">
        <fo:block>---------------</fo:block>
      </fo:static-content>
  </xsl:template>

</xsl:transform>