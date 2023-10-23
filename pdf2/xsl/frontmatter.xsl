<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <xsl:include href="attribute/id.xsl" />
  <xsl:include href="attribute/cgmark.xsl" />
  <xsl:include href="./group/reducedParaElemGroup.xsl" />
  <xsl:include href="./element/frontMatterList.xsl" />
  <xsl:include href="./group/part/dmRef.xsl" />

  <xsl:output method="xml" omit-xml-declaration="yes"/>

  <xsl:param name="dmOwner"/>
  <xsl:param name="absolute_path_csdbInput"></xsl:param>
  <xsl:param name="logo_ptdi"></xsl:param>
  <xsl:param name="absolute_asset_path"></xsl:param>
  <!-- <xsl:param name="resolve_dmCode_forXSLT"></xsl:param> -->
  <!-- <xsl:param name="tesfungsi"></xsl:param> -->

  <xsl:template match="dmodule">
    <xsl:apply-templates select="//content/frontMatter"/>
  </xsl:template>

  <xsl:template match="frontMatterTitlePage">
    <div>
      <!-- Logo 1 -->
      <div style="text-align:center">
        <img>
          <xsl:attribute name="src">
            <xsl:value-of select="$absolute_path_csdbInput"/>
            <xsl:value-of select="productIllustration/graphic[1]/@infoEntityIdent"/>
          </xsl:attribute>
          <xsl:if test="productIllustration/graphic[1]/@reproductionHeight">
            <xsl:attribute name="height">
              <xsl:value-of select="productIllustration/graphic[1]/@reproductionHeight"/>
            </xsl:attribute>
          </xsl:if>
        </img>
      </div>

      <!-- Doc No -->
      <div style="text-align:center;color:blue">
        <b>DOCUMENT NO. D661ND1001</b>
      </div>

      <!-- Logo 2 -->
      <div style="text-align:center">
        <img>
          <xsl:attribute name="src">
            <xsl:value-of select="$absolute_path_csdbInput"/>
            <xsl:value-of select="productIllustration/graphic[2]/@infoEntityIdent"/>
          </xsl:attribute>
          <xsl:if test="productIllustration/graphic[2]/@reproductionHeight">
            <xsl:attribute name="height">
              <xsl:value-of select="productIllustration/graphic[2]/@reproductionHeight"/>
            </xsl:attribute>
          </xsl:if>
        </img>
        <br/>
        <br/>
      </div>

      <!-- Title -->
      <div style="text-align:center;color:blue">
        <!-- <b style="font-size:14">PILOT OPERATING HANDBOOK <br/> AND <br/>  DGCA APPROVED AIRPLANE FLIGHT MANUAL</b> -->
        <b style="font-size:14">
          <xsl:value-of select="pmTitle"/>
        </b>
        <br/>
        <br/>
      </div>

      <!-- Serial and Registration No -->
      <div>
        <table cellpadding="10" style="width:100%">
          <tr>
            <td align="left" style="width:20%;">&#160;</td>
            <td align="left" style="width:60%;border:1px solid black">
              <div>
                <b style="font-size:11">Airplane Serial Number: ___</b>
              </div>
              <div>
                <b style="font-size:11">Airplane Registration: ___</b>
              </div>
                
            </td>
          <td align="left" style="width:20%;">&#160;</td>
          </tr>
        </table>
        <br/>
        <br/>
      </div>
      
      <!-- Address PTDI -->
      <div>
        <table style="width:100%">
          <tr>
            <td style="width:20%"><img src="{$logo_ptdi}" style="width:20mm"/></td>
            <td style="width:70%;font-size:7">
              <br/>
              <br/>
              <span>PT. DIRGANTARA INDONESIA</span>
              <br/>
              <span>Jl. Pajajaran 154 Bandung 40174 Indonesia, Phone 62-22-6054781, Fax. 62-22-6034521</span>
              <br/>
              <span>Email: techpubs@indonesian-aerospace.com</span>
            </td>
          </tr>
        </table>
        <!-- halaman 2 (genap): kosong -->
        <div style="page-break-before:always"></div>
      </div>


      <!-- halaman 3 (ganjil) -->
      <div style="page-break-before:always">

        <div style="text-align:center">
          <img src="{$logo_ptdi}" style="width:20mm"/>
        </div>

        <div style="text-align:center">            
          <h3 style="color:blue">
            <xsl:value-of select="pmTitle"/>
          </h3>
          <img height="10mm">
            <xsl:attribute name="src">
              <xsl:value-of select="$absolute_path_csdbInput"/>
              <xsl:value-of select="productIllustration/graphic[1]/@infoEntityIdent"/>
            </xsl:attribute>
          </img>
        </div>

        <div style="text-align:center">
          <span>(Serial Numbers ______)</span>
          <br/>
          <span>Type Certificate A118 (Indonesia)</span>
        </div>

        <div style="text-align:center">
          <span>Manufactured by PT DIRGANTARA INDONESIA</span>
          <br/>
          <span>Type Certificate held by PT DIRGANTARA INDONESIA</span>
        </div>

        <div>
          <h3 style="text-align:center">
            <xsl:apply-templates select="frontMatterInfo[@frontMatterInfoType = 'fmi51']/title"/>
            <xsl:call-template name="cgmark"/>
          </h3>
          <xsl:apply-templates select="frontMatterInfo[@frontMatterInfoType = 'fmi51']/reducedPara"/>
        </div>
        
        <!-- halaman 4 (genap): kosong -->
        <div style="page-break-before:always"></div>
      </div>

      <!-- halaman 5 -->
      <div style="page-break-before:always">
        <h3 style="text-align:center">
          <xsl:apply-templates select="frontMatterInfo[@frontMatterInfoType = 'fmi52']/title"/>
          <xsl:call-template name="cgmark"/>
        </h3>
        <xsl:text>&#160;</xsl:text>
        <xsl:apply-templates select="frontMatterInfo[@frontMatterInfoType = 'fmi52']/reducedPara"/>

        <!-- halaman 6 (genap): kosong -->
        <div style="page-break-before:always"></div>
      </div>
    </div>
  </xsl:template>

  <xsl:template match="frontMatterInfo">
    <div>
      <h6>
        <xsl:apply-templates select="title"/>
        <xsl:call-template name="cgmark"/>
      </h6>
      <xsl:apply-templates select="reducedPara"/>
    </div>
    <br/>
    <br/>
  </xsl:template>

  <xsl:template match="reducedPara">
    <p>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </p>
  </xsl:template>
  


</xsl:stylesheet>