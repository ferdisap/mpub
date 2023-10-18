<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <!-- ini dipanggil di frontmatter.xsl -->

  <xsl:output method="xml" omit-xml-declaration="yes"/>

  <!-- <xsl:param name="dmOwner"/> -->
  <!-- <xsl:param name="absolute_path_csdbInput"></xsl:param> -->
  <!-- <xsl:param name="logo_ptdi"></xsl:param> -->


  <xsl:template match="frontMatterList[@frontMatterType = 'fm02']">
    <div>
      <style>
        .table_leodm th{
          border-top:1px solid black;
          border-bottom:1px solid black;
        }

      </style>
      <h3 style="text-align:center"><xsl:value-of select="frontMatterSubList/title"/></h3>
      <span style="text-align:left"><xsl:apply-templates select="frontMatterSubList/reducedPara"/></span>
      
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
         <xsl:variable name="title">
          <xsl:call-template name="get_dmTitle"/>
         </xsl:variable>
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
            <!-- <td>&#160;</td> -->
            <td style="width:15%"><xsl:value-of select="php:function('Ptdi\Mpub\CSDB::resolve_issueDate', //identAndStatusSection/dmAddress/dmAddressItems/issueDate)"/></td>
            <td style="width:15%"><xsl:value-of select="php:function('Ptdi\Mpub\CSDB::get_applic_display_text', //identAndStatusSection/dmStatus/applic)"/></td>
          </tr>
        </xsl:for-each>
        </tbody>
      </table>

    </div>
  </xsl:template>

  <xsl:template name="get_dmTitle">
    <!-- <xsl:value-of select="$absolute_path_csdbInput" -->
  </xsl:template>


</xsl:stylesheet>