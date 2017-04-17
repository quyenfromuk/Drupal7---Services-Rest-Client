# Drupal7---Services-Rest-Client
REST data exchange with Drupal Services 3 module

Description:
NYU Shanghai Custom Drupal 7.x module that provides distributed client/server REST content sharing across 2 or more Drupal websites (works in collaboration with services, services entity modules)

By creating action resources that define which node types to share, you can choose which types of content to push to other portal sites.  For example, filter content by their content type and term references.  Turn action resources “on” and “off” on the fly for more control on data sync

Examples of content type that can be pushed
Files: txt, pdf, doc, docx, ppt, zip, rar, mp4, mov
Nodes
Vocabularies, terms
Nodes with reference to vocabularies and terms

If you have content that already exists on other portal sites, you can choose to link them to your current site, so that changes in one will update on the other.  The system will smart match potential content to link as defined in the action resource.  
Pick and choose individual pieces of content to link. Once the contents are linked, editing them on the current site will update those changes on the other site.  Once they are linked you may view exiting data links with other portal sites

Data Architecture:
Decide each portal site for push and pull content.  Configure action resources in admin UI.  Once this is setup, you can the edit content and changes will be made on other portal sites

Data Migration usage:
It can also be used for data migration.  You can bulk migrate date to other protal sites on the fly.  Accumulate content on one site and when you’re ready bulk push them to another portal site.  The same content appear on other portal site, with the actual content data stored in its own local database

Amazon S3 File Storage:
Pushed content files may be stored on Amazon S3.  However, portal site pulling content will only store and reference files natively within its server instance
