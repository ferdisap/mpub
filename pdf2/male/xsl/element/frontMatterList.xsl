<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <!-- ini dipanggil di frontmatter.xsl -->

  <xsl:output method="xml" omit-xml-declaration="yes"/>

  <!-- <xsl:param name="dmOwner"/> -->
  <!-- <xsl:param name="absolute_path_csdbInput"></xsl:param> -->
  <!-- <xsl:param name="logo_ptdi"></xsl:param> -->


  <!-- fm02 = LEODM -->
  <xsl:template match="frontMatterList[@frontMatterType = 'fm02']">
    <div>
      <style>
        .table_leodm th{
          border-top:1px solid black;
          border-bottom:1px solid black;
          font-weight:bold;
        }
        .table_leodm {
          border-bottom:1px solid black
        }
      </style>
      <b style="text-align:center;font-size:12"><xsl:value-of select="frontMatterSubList/title"/></b>

      <div style="text-align:left">
        <p>
          <xsl:text>The listed documents are included in issue </xsl:text>
          <xsl:value-of select="issueInfo/@issueNumber"/>
          <xsl:text>, dated </xsl:text>
          <xsl:value-of select="php:function('Ptdi\Mpub\CSDB::resolve_issueDate', issueDate)"/>
          <xsl:text>, of this publication.</xsl:text>
        </p>
        <p>
          <xsl:text>
            C = Changed Data Module#ln;N = New Data Module 
          </xsl:text>
        </p>
      </div>
      
      <table class="table_leodm" style="width:100%;font-size:6;line-height:2">
        <thead>
          <tr>
            <th style="width:20%">Title</th>
            <th style="width:35%">DM Code</th>
            <th style="width:5%">&#160;</th>
            <th style="width:15%">Issue Date</th>
            <th style="width:15%">Applicability</th>
          </tr>
        </thead>
        <tbody>
         <xsl:for-each select="frontMatterSubList/frontMatterDmEntry">
          <tr>
            <td style="width:20%"><xsl:value-of select="php:function('Ptdi\Mpub\CSDB::resolve_dmTitle', //identAndStatusSection/dmAddress/dmAddressItems/dmTitle, 'techname')"/></td>
            <td style="width:35%"><xsl:value-of select="php:function('Ptdi\Mpub\CSDB::resolve_dmCode', //identAndStatusSection/dmAddress/dmIdent/dmCode)"/></td>
            <td style="width:5%">
              &#160;
              <xsl:if test="@issueType">
                <xsl:value-of select="php:function('Ptdi\Mpub\CSDB::resolve_issueType', @issueType)"/>
              </xsl:if>
              &#160;
            </td>
            <td style="width:15%"><xsl:value-of select="php:function('Ptdi\Mpub\CSDB::resolve_issueDate', //identAndStatusSection/dmAddress/dmAddressItems/issueDate)"/></td>
            <!-- <td style="width:15%"><xsl:value-of select="php:function('Ptdi\Mpub\CSDB::get_applic_display_text', //identAndStatusSection/dmStatus/applic)"/></td> -->
            <td style="width:15%"><xsl:value-of select="php:function('Ptdi\Mpub\Pdf2\DMC::getApplicability', '', 'first')"/></td>
          </tr>
        </xsl:for-each>
        </tbody>
      </table>

    </div>
  </xsl:template>

  <!-- fm03 = HIGH -->
  <xsl:template match="frontMatterList[@frontMatterType = 'fm03']">
    <div>
      <style>
        .table_high th{
          border-top:1px solid black;
          border-bottom:1px solid black;
          font-weight:bold
        }
        .table_high {
          border-bottom:1px solid black
        }
      </style>
      <b style="text-align:center;font-size:12"><xsl:value-of select="frontMatterSubList/title"/></b>

      <div style="text-align:left">
        <br/>
        <b style="font-size:10;text-align:center"><i>Issue </i><xsl:value-of select="issueInfo/@issueNumber"/></b>
        <p>
          <xsl:text>The listed changes are introduced in issue </xsl:text>
          <xsl:value-of select="issueInfo/@issueNumber"/>
          <xsl:text>, dated </xsl:text>
          <xsl:value-of select="php:function('Ptdi\Mpub\CSDB::resolve_issueDate', issueDate)"/>
          <xsl:text>, of this publication.</xsl:text>
        </p>
        <p>
          <xsl:apply-templates select="frontMatterSubList/reducedPara"/>
        </p>
      </div>
      
      <table class="table_high" style="width:100%;font-size:6;line-height:2;">
        <thead>
          <tr>
            <th style="width:35%">DM Code</th>
            <th style="width:65%">Reason for update</th>
          </tr>
        </thead>
        <tbody>
         <xsl:for-each select="frontMatterSubList/frontMatterDmEntry">
          <tr>
            <td style="width:35%"><xsl:value-of select="php:function('Ptdi\Mpub\CSDB::resolve_dmCode', //identAndStatusSection/dmAddress/dmIdent/dmCode)"/></td>
            <td style="width:65%;text-align:left">
              
              <xsl:variable name="issueType"><xsl:value-of select="php:function('Ptdi\Mpub\CSDB::resolve_issueType', @issueType, 'sentencecase')"/></xsl:variable>
              <xsl:if test="not($issueType = '')">
                <xsl:value-of select="$issueType"/>
                <xsl:text>.&#160;</xsl:text>
              </xsl:if>

              <xsl:variable name="file">
                <xsl:variable name="ident">
                  <xsl:apply-templates select="dmRef/dmRefIdent">
                    <xsl:with-param name="prefix">DMC-</xsl:with-param>
                  </xsl:apply-templates>
                </xsl:variable>
                <xsl:value-of select="php:function('strtoupper',$ident)"/>
                <xsl:text>.xml</xsl:text>
              </xsl:variable>
              <xsl:comment>Character "file:://" dan slash "\" diganti menjadi '' dan "/" pada  $absolute_path_csdbInput = file://D:\application\php-app\mpub\tes2\</xsl:comment>
              <xsl:variable name="path" select="concat( (php:function('preg_replace','/\\/', '/', (php:function('preg_replace', '/file:\/\//','', $absolute_path_csdbInput)))), $file )"/>
              <xsl:variable name="doc" select="document($path)"/>
              <xsl:if test="$doc">
                <xsl:value-of select="$doc//dmodule/identAndStatusSection/dmStatus/reasonForUpdate[last()]"/>
              </xsl:if>
              
              <xsl:text>&#160;</xsl:text>
            </td>
          </tr>
        </xsl:for-each>
        </tbody>
      </table>

    </div>
  </xsl:template>
  
</xsl:stylesheet>