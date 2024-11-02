<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:fox="http://xmlgraphics.apache.org/fop/extensions"
  xmlns:php="http://php.net/xsl">

  <xsl:output method="xml" />
  
  <xsl:param name="filename"/>
  <!-- <xsl:param name="base_uri"/> -->
  <xsl:param name="config_uri"/>
  <xsl:param name="configurableValues_uri"/>
  <xsl:param name="csdb_path"/>
  <!-- <xsl:param name="CSDBObject_serialize"/> -->

  <xsl:include href="./master/default-A4.xsl" />
  <xsl:include href="./master/header_default.xsl" />
  <xsl:include href="./master/footer_default.xsl" />
  <xsl:include href="./dmodule.xsl" />
  <xsl:include href="./pm.xsl"/>
  <xsl:include href="./master/region.xsl" />
  <xsl:include href="./master/default-pm.xsl" />
  <xsl:include href="../generalXSL/other/other.xsl" />
  
  <xsl:include href="../generalXSL/identStatus/All-Authority.xsl" />
  <xsl:include href="../generalXSL/identStatus/All-Enterprise.xsl" />
  <xsl:include href="../generalXSL/identStatus/All-Security.xsl" />
  <xsl:include href="../generalXSL/identStatus/dmTitle.xsl" />
  <xsl:include href="../generalXSL/identStatus/remarks.xsl" />
  <xsl:include href="../generalXSL/identStatus/Style-dmTitle.xsl" />
  <xsl:include href="../generalXSL/content/content.xsl" />
  <xsl:include href="../generalXSL/content/applicability/All.xsl" />
  <xsl:include href="../generalXSL/content/captionGroups/All.xsl" />
  <xsl:include href="../generalXSL/content/changeMarking/All.xsl" />
  <xsl:include href="../generalXSL/content/commonInformation/commonInfo.xsl" />
  <xsl:include href="../generalXSL/content/commonInformationRepository/All-AccessPointRepository.xsl" />
  <xsl:include href="../generalXSL/content/commonInformationRepository/All-CircuitBreakerRepository.xsl" />
  <xsl:include href="../generalXSL/content/commonInformationRepository/All-ControlIndicatorRepository.xsl" />
  <xsl:include href="../generalXSL/content/commonInformationRepository/All-ZoneRepository.xsl" />
  <xsl:include href="../generalXSL/content/commonInformationRepository/comrep.xsl" />
  <xsl:include href="../generalXSL/content/crewOperatorInformation/All.xsl" />
  <xsl:include href="../generalXSL/content/descriptiveInformation/Style-levelledPara.xsl" />
  <xsl:include href="../generalXSL/content/descriptiveInformation/description.xsl" />
  <xsl:include href="../generalXSL/content/descriptiveInformation/levelledPara.xsl" />
  <xsl:include href="../generalXSL/content/figuresMultimediaFoldouts/All.xsl" />
  <xsl:include href="../generalXSL/content/figuresMultimediaFoldouts/Style-icn.xsl" />
  <xsl:include href="../generalXSL/content/frontMatter/All-FrontMatterTitlePage.xsl" />
  <xsl:include href="../generalXSL/content/frontMatter/POH-FrontMatterTitlePage.xsl" />
  <xsl:include href="../generalXSL/content/frontMatter/All-FrontMatterList.xsl" />
  <xsl:include href="../generalXSL/content/frontMatter/Highlights.xsl" />
  <xsl:include href="../generalXSL/content/frontMatter/HighlightsAndUpdating.xsl" />
  <xsl:include href="../generalXSL/content/frontMatter/Leodm.xsl" />
  <xsl:include href="../generalXSL/content/frontMatter/Updating.xsl" />
  <xsl:include href="../generalXSL/content/frontMatter/frontMatter.xsl" />
  <xsl:include href="../generalXSL/content/lists/All.xsl" />
  <xsl:include href="../generalXSL/content/lists/Style-list.xsl" />
  <xsl:include href="../generalXSL/content/maintenancePlanningInformation/All-MaintPlanning.xsl" />
  <xsl:include href="../generalXSL/content/maintenancePlanningInformation/taskDefinition_for_structuralInspectionProgram.xsl" />
  <xsl:include href="../generalXSL/content/maintenancePlanningInformation/taskDefinition_for_systemAndPowerPlantInspectionProgram.xsl" />
  <xsl:include href="../generalXSL/content/maintenancePlanningInformation/taskDefinition_for_zonalInspectionProgram.xsl" />
  <xsl:include href="../generalXSL/content/maintenancePlanningInformation/taskDefinition_for_timeLimitProgram.xsl" />
  <xsl:include href="../generalXSL/content/preliminaryRequirementsAndRequirementsAfterJobCompletion/identNumber.xsl" />
  <xsl:include href="../generalXSL/content/referencing/dmRef.xsl" />
  <xsl:include href="../generalXSL/content/referencing/externalPubRef.xsl" />
  <xsl:include href="../generalXSL/content/referencing/functionalItemRef.xsl" />
  <xsl:include href="../generalXSL/content/referencing/internalRef.xsl" />
  <xsl:include href="../generalXSL/content/referencing/pmRef.xsl" />
  <xsl:include href="../generalXSL/content/referencing/refs.xsl" />
  <xsl:include href="../generalXSL/content/tables/All.xsl" />
  <xsl:include href="../generalXSL/content/tables/Style-table.xsl" />
  <xsl:include href="../generalXSL/content/tables/Style-tgroup.xsl" />
  <xsl:include href="../generalXSL/content/tables/Style-row.xsl" />
  <xsl:include href="../generalXSL/content/tables/Style-entry.xsl" />
  <xsl:include href="../generalXSL/content/textElements/All.xsl" />
  <xsl:include href="../generalXSL/content/textElements/Style-para.xsl" />
  <xsl:include href="../generalXSL/content/textElements/accessPointRef.xsl" />
  <xsl:include href="../generalXSL/content/textElements/circuitBreakerRef.xsl" />
  <xsl:include href="../generalXSL/content/textElements/controlIndicatorRef.xsl" />
  <xsl:include href="../generalXSL/content/textElements/footnoteRef.xsl" />
  <xsl:include href="../generalXSL/content/textElements/name.xsl" />
  <xsl:include href="../generalXSL/content/textElements/para.xsl" />
  <xsl:include href="../generalXSL/content/textElements/partRef.xsl" />
  <xsl:include href="../generalXSL/content/textElements/zoneRef.xsl" />
  <xsl:include href="../generalXSL/content/titles/All.xsl" />
  <xsl:include href="../generalXSL/content/titles/Style-title.xsl" />
  <xsl:include href="../generalXSL/content/warningCautionNote/All.xsl" />
  <xsl:include href="../generalXSL/content/warningCautionNote/Attention.xsl" />
  <xsl:include href="../generalXSL/content/warningCautionNote/Style-warningcautionnote.xsl" />
  <xsl:include href="../generalXSL/content/wiringInformation/amperage.xsl" />
  <xsl:include href="../generalXSL/other/Style.xsl" />
  <xsl:include href="../generalXSL/other/id.xsl" />
  <xsl:include href="../generalXSL/other/position.xsl" />
  
  <xsl:variable name="ConfigXML" select="document($config_uri)"/>

  <xsl:variable name="masterName">
    <xsl:choose>
      <xsl:when test="pm">
        <xsl:variable name="pt" select="string(pm/@pmType)" />
        <xsl:variable name="name" select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','pmType', string($pt), 'abbreviation', 'default')" />
        <xsl:choose>
          <xsl:when test="$name">
            <xsl:value-of select="$name"/>
          </xsl:when>
          <xsl:otherwise>default-pm</xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text>default-A4</xsl:text>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:variable name="colorLink">blue</xsl:variable>

  <xsl:variable name="warningPath"><xsl:value-of select="$ConfigXML/config/output/method[@type='pdf']/assets"/>/warningBackground.png</xsl:variable>
  <xsl:variable name="cautionPath"><xsl:value-of select="$ConfigXML/config/output/method[@type='pdf']/assets"/>/cautionBackground.png</xsl:variable>
  <xsl:variable name="controlAutoritySymbolPath"><xsl:value-of select="$ConfigXML/config/output/method[@type='pdf']/assets"/>/controlAuthoritySymbol.png</xsl:variable>

  <xsl:template match="/">
    <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Pdf::set_PDF_MasterName', $masterName)"/>
    <fo:root font-family="Arial">
      <xsl:call-template name="setPageMaster">
        <xsl:with-param name="masterName" select="$masterName" />
      </xsl:call-template>

      <xsl:call-template name="setBookmark">
        <xsl:with-param name="masterName" select="$masterName" />
      </xsl:call-template>

      <xsl:call-template name="setPageSequence">
        <xsl:with-param name="masterName" select="$masterName" />
      </xsl:call-template>
    </fo:root>
  </xsl:template>

  <xsl:template name="setPageMaster">
    <xsl:param name="masterName" />
    <xsl:choose>
      <xsl:when test="$masterName = 'default-A4'">
        <xsl:call-template name="pageMasterByDefaultA4">
          <xsl:with-param name="masterName" select="$masterName"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="pageMasterByPt">
          <xsl:with-param name="masterName" select="$masterName" />
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="setPageSequence">
    <xsl:param name="masterName"/>
    <xsl:choose>
      <xsl:when test="name(*) = 'pm'">
        <xsl:apply-templates select="pm">
          <xsl:with-param name="masterReference" select="$masterName"/>
        </xsl:apply-templates>
      </xsl:when>
      <xsl:when test="name(*) = 'dmodule'">
        <xsl:apply-templates select="dmodule">
          <xsl:with-param name="masterReference" select="$masterName"/>
        </xsl:apply-templates>
      </xsl:when>
      <xsl:otherwise>
        <fo:page-sequence master-reference="default-A4">
          <fo:flow flow-name="body">
            <fo:block>Nothing to displayed of &#60;<xsl:value-of select="name(*)"/>&#62;</fo:block>
          </fo:flow>
        </fo:page-sequence>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="setBookmark">
    <fo:bookmark-tree/>
  </xsl:template>

  <!-- -->
  <xsl:template name="get_PDF_MasterName">
    <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Pdf::get_PDF_MasterName')"/>
  </xsl:template>

  <!-- dipanggil di content.xsl dan Style-table.xsl-->
  <xsl:template name="get_orientation">
    <xsl:param name="masterName" select="$masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/@orientation)"/>
  </xsl:template>

  <!-- dipanggil di content.xsl dan style-title.xsl-->
  <xsl:template name="get_stIndent">
    <xsl:param name="masterName" select="$masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name=$masterName]/@start-indent)"/>
  </xsl:template>

  <!-- dipanggil di warningCautionNote/All.xsl  -->
  <!-- jika kedepannya membutuhkan masterName untuk setiap template yang digunakan di dmodule/content, maka masterName harus ditaruh di CSDBStatic agar tidak merepotkan transfer parameter terus -->
  <xsl:template name="get_blockIndent">
    <xsl:text>0cm</xsl:text>
  </xsl:template>
  
  <!-- dipanggil di content.xsl, pm.xsl -->
  <xsl:template name="get_defaultFontSize">
    <xsl:param name="masterName" select="$masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/@default-font-size)"/>
  </xsl:template>

  <!-- depreciated, diganti template name "get_layout_width karena nama templatenya saja -->
  <!-- dipanggil di default-A4.xsl -->
  <xsl:template name="get_width">
    <xsl:param name="masterName" select="$masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/@width)"/>
  </xsl:template>

  <!-- depreciated, diganti template name "get_layout_height" karena nama templatenya saja -->
  <!-- dipanggil di default-A4.xsl -->
  <xsl:template name="get_height">
    <xsl:param name="masterName" select="$masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/@height)"/>
  </xsl:template>

  <!-- dipanggil di changeMark/All.xsl -->
  <xsl:template name="get_cgmarkOffset">
    <xsl:param name="masterName" select="$masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/@cgmark-offset)"/>
  </xsl:template>

  <xsl:template name="get_layout_unit_length">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/@length-unit)"/>
  </xsl:template>

  <xsl:template name="get_layout_unit_area">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/@area-unit)"/>
  </xsl:template>

  <xsl:template name="get_layout_width">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/@width)"/>
  </xsl:template>

  <xsl:template name="get_layout_height">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/@height)"/>
  </xsl:template>

  <xsl:template name="get_layout_masterName_for_odd">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/master-name_for_odd)"/>
  </xsl:template>
  
  <xsl:template name="get_layout_marginTop_for_odd">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-top_for_odd)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginBottom_for_odd">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-bottom_for_odd)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginLeft_for_odd">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-left_for_odd)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginRight_for_odd">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-right_for_odd)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginTop_for_odd_body">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-top_for_odd_body)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginBottom_for_odd_body">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-bottom_for_odd_body)"/>
  </xsl:template>
  
  <xsl:template name="get_layout_marginRight_for_odd_body">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-right_for_odd_body)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginLeft_for_odd_body">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-left_for_odd_body)"/>
  </xsl:template>

  <xsl:template name="get_layout_extent_for_odd_header">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/extent_for_odd_header)"/>
  </xsl:template>

  <xsl:template name="get_layout_extent_for_odd_footer">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/extent_for_odd_footer)"/>
  </xsl:template>

  <xsl:template name="get_layout_masterName_for_even">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/master-name_for_even)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginTop_for_even">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-top_for_even)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginBottom_for_even">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-bottom_for_even)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginLeft_for_even">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-left_for_even)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginRight_for_even">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-right_for_even)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginTop_for_even_body">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-top_for_even_body)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginBottom_for_even_body">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-bottom_for_even_body)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginLeft_for_even_body">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-left_for_even_body)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginRight_for_even_body">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-right_for_even_body)"/>
  </xsl:template>

  <xsl:template name="get_layout_extent_for_even_header">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/extent_for_even_header)"/>
  </xsl:template>

  <xsl:template name="get_layout_extent_for_even_footer">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/extent_for_even_footer)"/>
  </xsl:template>  

  <xsl:template name="get_layout_masterName_for_leftBlank">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/master-name_for_leftBlank)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginTop_for_leftBlank">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-top_for_leftBlank)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginBottom_for_leftBlank">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-bottom_for_leftBlank)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginLeft_for_leftBlank">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-left_for_leftBlank)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginRight_for_leftBlank">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-right_for_leftBlank)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginTop_for_leftBlank_body">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-top_for_leftBlank_body)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginBottom_for_leftBlank_body">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-bottom_for_leftBlank_body)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginLeft_for_leftBlank_body">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-left_for_leftBlank_body)"/>
  </xsl:template>

  <xsl:template name="get_layout_marginRight_for_leftBlank_body">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/margin-right_for_leftBlank_body)"/>
  </xsl:template>

  <xsl:template name="get_layout_extent_for_leftBlank_header">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/extent_for_leftBlank_header)"/>
  </xsl:template>

  <xsl:template name="get_layout_extent_for_leftBlank_footer">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/extent_for_leftBlank_footer)"/>
  </xsl:template>

  <xsl:template name="get_layout_regionName_for_body">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/region-name_for_body)"/>
  </xsl:template>

  <xsl:template name="get_layout_regionName_for_bodyLeftBlank">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/region-name_for_bodyLeftBlank)"/>
  </xsl:template>

  <xsl:template name="get_layout_regionName_for_headerOdd">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/region-name_for_headerOdd)"/>
  </xsl:template>

  <xsl:template name="get_layout_regionName_for_footerOdd">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/region-name_for_footerOdd)"/>
  </xsl:template>

  <xsl:template name="get_layout_regionName_for_headerEven">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/region-name_for_headerEven)"/>
  </xsl:template>

  <xsl:template name="get_layout_regionName_for_footerEven">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/region-name_for_footerEven)"/>
  </xsl:template>

  <xsl:template name="get_layout_regionName_for_headerLeftBlank">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/region-name_for_headerLeftBlank)"/>
  </xsl:template>

  <xsl:template name="get_layout_regionName_for_footerLeftBlank">
    <xsl:param name="masterName"/>
    <xsl:value-of select="string($ConfigXML/config/layout[@type='pdf' and @master-name = $masterName]/simple-page-master/region-name_for_footerLeftBlank)"/>
  </xsl:template>
</xsl:transform>