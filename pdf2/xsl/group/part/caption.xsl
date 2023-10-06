<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:template match="caption">
    foo<table style="border:1px solid red; width:30mm">
      <tr style="height:30mm">
        <td style="height:10mm">
          foobar
        </td>
      </tr>
    </table>
  </xsl:template>

</xsl:stylesheet>